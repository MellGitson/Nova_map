<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Request\LoginRequest;
use App\Dto\Request\RegisterRequest;
use App\Entity\User;
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
    private const BLOCK_DURATION = '+15 minutes';
    private const MAX_ATTEMPTS   = 5;

    public function __construct(
        private readonly EntityManagerInterface       $em,
        private readonly UserPasswordHasherInterface  $hasher,
        private readonly JWTTokenManagerInterface     $jwtManager,
        private readonly TokenFamilyService           $tokenFamily,
        private readonly ValidatorInterface           $validator,
        private readonly SerializerInterface          $serializer,
    ) {}

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $dto    = $this->serializer->deserialize($request->getContent(), RegisterRequest::class, 'json');
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
             ->setEmailVerifie(true)
             ->setConsentementRgpd(new \DateTimeImmutable());

        $this->em->persist($user);
        $this->em->flush();

        return $this->json($this->userPayload($user), Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $dto    = $this->serializer->deserialize($request->getContent(), LoginRequest::class, 'json');
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            return $this->validationErrors($errors);
        }

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => strtolower($dto->email)]);

        if ($user && $user->isBloque()) {
            return $this->json(['message' => 'Compte temporairement bloqué. Réessayez dans 15 minutes.'], Response::HTTP_TOO_MANY_REQUESTS);
        }

        if (!$user || !$this->hasher->isPasswordValid($user, $dto->password)) {
            if ($user) {
                $user->incrementTentativesLogin();
                if ($user->getTentativesLogin() >= self::MAX_ATTEMPTS) {
                    $user->setBloqueJusqua(new \DateTimeImmutable(self::BLOCK_DURATION));
                    $user->resetTentativesLogin();
                }
                $this->em->flush();
            }
            return $this->json(['message' => 'Email ou mot de passe incorrect.'], Response::HTTP_UNAUTHORIZED);
        }

        $user->resetTentativesLogin()->setBloqueJusqua(null);
        $this->em->flush();

        $accessToken  = $this->jwtManager->create($user);
        $refreshToken = $this->tokenFamily->create($user);

        $response = $this->json([
            'access_token' => $accessToken,
            'user'         => $this->userPayload($user),
        ]);

        $response->headers->setCookie($this->makeRefreshCookie($refreshToken->getToken()));

        return $response;
    }

    #[Route('/refresh', name: 'refresh', methods: ['POST'])]
    public function refresh(Request $request): JsonResponse
    {
        $rawToken = $request->cookies->get('refresh_token');

        if (!$rawToken) {
            return $this->json(['message' => 'Refresh token manquant.'], Response::HTTP_UNAUTHORIZED);
        }

        $existing = $this->em->getRepository(\App\Entity\RefreshToken::class)->findOneBy(['token' => $rawToken]);
        if (!$existing) {
            return $this->json(['message' => 'Token invalide.'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $newRefresh  = $this->tokenFamily->consume($rawToken, $existing->getUtilisateur());
            $accessToken = $this->jwtManager->create($existing->getUtilisateur());
        } catch (\RuntimeException $e) {
            $response = $this->json(['message' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
            $response->headers->clearCookie('refresh_token', '/', null, true, true);
            return $response;
        }

        $response = $this->json(['access_token' => $accessToken]);
        $response->headers->setCookie($this->makeRefreshCookie($newRefresh->getToken()));

        return $response;
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(#[CurrentUser] ?User $user): JsonResponse
    {
        if ($user) {
            $this->tokenFamily->revokeAllForUser($user);
        }

        $response = $this->json(['message' => 'Déconnexion réussie.']);
        $response->headers->clearCookie('refresh_token', '/', null, true, true);

        return $response;
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(#[CurrentUser] User $user): JsonResponse
    {
        return $this->json($this->userPayload($user));
    }

    /** DELETE /api/auth/account — droit à l'oubli RGPD */
    #[Route('/account', name: 'delete_account', methods: ['DELETE'])]
    public function deleteAccount(#[CurrentUser] User $user): JsonResponse
    {
        $user->setEmail('deleted_' . $user->getId() . '@supprime.local')
             ->setNom('Utilisateur supprimé')
             ->setNumeroPermis(null)
             ->setTelephone(null)
             ->setDateSuppression(new \DateTimeImmutable())
             ->setEmailVerifie(false);

        $this->tokenFamily->revokeAllForUser($user);
        $this->em->flush();

        $response = $this->json(['message' => 'Compte supprimé conformément au RGPD.']);
        $response->headers->clearCookie('refresh_token', '/', null, true, true);
        return $response;
    }

    private function userPayload(User $user): array
    {
        return [
            'id'            => $user->getId(),
            'email'         => $user->getEmail(),
            'nom'           => $user->getNom(),
            'roles'         => $user->getRoles(),
            'email_verifie' => $user->isEmailVerifie(),
            'date_creation' => $user->getDateCreation()->format(\DateTimeInterface::ATOM),
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
