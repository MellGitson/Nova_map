<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Request\LoginRequest;
use App\Dto\Request\RegisterRequest;
use App\Entity\JournalAudit;
use App\Entity\User;
use App\Service\AuditService;
use App\Service\TokenFamilyService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/auth', name: 'api_auth_')]
final class AuthController extends AbstractController
{
    // Durée de blocage après 5 échecs (15 min)
    private const BLOCK_DURATION = '+15 minutes';
    private const MAX_ATTEMPTS   = 5;

    public function __construct(
        private readonly EntityManagerInterface       $em,
        private readonly UserPasswordHasherInterface  $hasher,
        private readonly JWTTokenManagerInterface     $jwtManager,
        private readonly TokenFamilyService           $tokenFamily,
        private readonly AuditService                 $audit,
        private readonly ValidatorInterface           $validator,
        private readonly SerializerInterface          $serializer,
    ) {}

    // -------------------------------------------------------------------------
    // POST /api/auth/register
    // -------------------------------------------------------------------------
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $dto = $this->serializer->deserialize($request->getContent(), RegisterRequest::class, 'json');
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            return $this->validationErrors($errors);
        }

        $existing = $this->em->getRepository(User::class)->findOneBy(['email' => strtolower($dto->email)]);
        if ($existing) {
            return $this->json(['message' => 'Un compte existe déjà avec cet email.'], Response::HTTP_CONFLICT);
        }

        $user = new User();
        $user->setEmail($dto->email)
             ->setNom($dto->nom)
             ->setPassword($this->hasher->hashPassword($user, $dto->password))
             ->setEmailVerifie(true) // TODO: confirmation email (post-MVP)
             ->setConsentementRgpd(new \DateTimeImmutable());

        $this->em->persist($user);
        $this->em->flush();

        $this->audit->log('USER_REGISTER', 'utilisateur', $user->getId(), JournalAudit::NIVEAU_INFO, null, $user);

        return $this->json($this->userPayload($user), Response::HTTP_CREATED);
    }

    // -------------------------------------------------------------------------
    // POST /api/auth/login
    // -------------------------------------------------------------------------
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $dto    = $this->serializer->deserialize($request->getContent(), LoginRequest::class, 'json');
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            return $this->validationErrors($errors);
        }

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => strtolower($dto->email)]);

        // Vérification blocage rate-limiting
        if ($user && $user->isBloque()) {
            $this->audit->log('LOGIN_BLOCKED', 'utilisateur', $user->getId(), JournalAudit::NIVEAU_WARNING, null, $user);
            return $this->json(['message' => 'Compte temporairement bloqué. Réessayez dans 15 minutes.'], Response::HTTP_TOO_MANY_REQUESTS);
        }

        if (!$user || !$this->hasher->isPasswordValid($user, $dto->password)) {
            if ($user) {
                $user->incrementTentativesLogin();
                if ($user->getTentativesLogin() >= self::MAX_ATTEMPTS) {
                    $user->setBloqueJusqua(new \DateTimeImmutable(self::BLOCK_DURATION));
                    $user->resetTentativesLogin();
                    $this->audit->log('LOGIN_ACCOUNT_LOCKED', 'utilisateur', $user->getId(), JournalAudit::NIVEAU_WARNING, null, $user);
                }
                $this->em->flush();
            }
            $this->audit->log('LOGIN_FAILURE', 'utilisateur', null, JournalAudit::NIVEAU_WARNING);
            return $this->json(['message' => 'Email ou mot de passe incorrect.'], Response::HTTP_UNAUTHORIZED);
        }

        // Succès : reset tentatives
        $user->resetTentativesLogin()->setBloqueJusqua(null);
        $this->em->flush();

        $accessToken  = $this->jwtManager->create($user);
        $refreshToken = $this->tokenFamily->create($user);

        $this->audit->log('LOGIN_SUCCESS', 'utilisateur', $user->getId(), JournalAudit::NIVEAU_INFO, null, $user);

        $response = $this->json([
            'access_token' => $accessToken,
            'user'         => $this->userPayload($user),
        ]);

        $response->headers->setCookie($this->makeRefreshCookie($refreshToken->getToken()));

        return $response;
    }

    // -------------------------------------------------------------------------
    // POST /api/auth/refresh
    // -------------------------------------------------------------------------
    #[Route('/refresh', name: 'refresh', methods: ['POST'])]
    public function refresh(Request $request): JsonResponse
    {
        $rawToken = $request->cookies->get('refresh_token');

        if (!$rawToken) {
            return $this->json(['message' => 'Refresh token manquant.'], Response::HTTP_UNAUTHORIZED);
        }

        // Identifier l'utilisateur via le token (sans JWT valide ici)
        $existing = $this->em->getRepository(\App\Entity\RefreshToken::class)->findOneBy(['token' => $rawToken]);
        if (!$existing) {
            return $this->json(['message' => 'Token invalide.'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $newRefresh  = $this->tokenFamily->consume($rawToken, $existing->getUtilisateur());
            $accessToken = $this->jwtManager->create($existing->getUtilisateur());
        } catch (\RuntimeException $e) {
            $this->audit->log('REFRESH_TOKEN_INVALID', 'refresh_token', null, JournalAudit::NIVEAU_CRITICAL);
            $response = $this->json(['message' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
            $response->headers->clearCookie('refresh_token', '/', null, true, true);
            return $response;
        }

        $response = $this->json(['access_token' => $accessToken]);
        $response->headers->setCookie($this->makeRefreshCookie($newRefresh->getToken()));

        return $response;
    }

    // -------------------------------------------------------------------------
    // POST /api/auth/logout
    // -------------------------------------------------------------------------
    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(Request $request, #[CurrentUser] ?User $user): JsonResponse
    {
        if ($user) {
            $this->tokenFamily->revokeAllForUser($user);
            $this->audit->log('LOGOUT', 'utilisateur', $user->getId(), JournalAudit::NIVEAU_INFO, null, $user);
        }

        $response = $this->json(['message' => 'Déconnexion réussie.']);
        $response->headers->clearCookie('refresh_token', '/', null, true, true);

        return $response;
    }

    // -------------------------------------------------------------------------
    // GET /api/auth/me
    // -------------------------------------------------------------------------
    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(#[CurrentUser] User $user): JsonResponse
    {
        return $this->json($this->userPayload($user));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------
    private function userPayload(User $user): array
    {
        return [
            'id'               => $user->getId(),
            'email'            => $user->getEmail(),
            'nom'              => $user->getNom(),
            'roles'            => $user->getRoles(),
            'email_verifie'    => $user->isEmailVerifie(),
            'date_creation'    => $user->getDateCreation()->format(\DateTimeInterface::ATOM),
        ];
    }

    private function makeRefreshCookie(string $token): Cookie
    {
        return Cookie::create('refresh_token')
            ->withValue($token)
            ->withHttpOnly(true)
            ->withSecure(true)
            ->withSameSite('strict')
            ->withPath('/')
            ->withExpires(new \DateTimeImmutable('+7 days'));
    }

    private function validationErrors(mixed $errors): JsonResponse
    {
        $messages = [];
        foreach ($errors as $error) {
            $messages[$error->getPropertyPath()] = $error->getMessage();
        }
        return $this->json(['errors' => $messages], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
