<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\RefreshToken;
use App\Entity\User;
use App\Repository\RefreshTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Token Family Pattern :
 * - Chaque famille partage un UUID.
 * - Un token refresh ne peut être utilisé qu'une seule fois.
 * - Si un token déjà consommé est réutilisé → toute la famille est révoquée.
 */
final class TokenFamilyService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RefreshTokenRepository $repo,
        private readonly int                    $ttlSeconds = 604800,
    ) {}

    public function create(User $user, ?string $familleId = null): RefreshToken
    {
        $token = new RefreshToken();
        $token->setUtilisateur($user)
              ->setToken(bin2hex(random_bytes(32)))
              ->setFamilleId($familleId ?? Uuid::v4()->toRfc4122())
              ->setExpireA(new \DateTimeImmutable("+{$this->ttlSeconds} seconds"));

        $this->em->persist($token);
        $this->em->flush();

        return $token;
    }

    /**
     * @throws \RuntimeException si le token est invalide, révoqué, expiré ou réutilisé.
     */
    public function consume(string $rawToken, User $user): RefreshToken
    {
        $token = $this->repo->findOneBy(['token' => $rawToken]);

        if (!$token) {
            throw new \RuntimeException('Token introuvable.');
        }

        if ($token->isConsomme()) {
            $this->revokeFamily($token->getFamilleId());
            throw new \RuntimeException('Réutilisation détectée — famille révoquée.');
        }

        if ($token->isRevoque()) {
            throw new \RuntimeException('Token révoqué.');
        }

        if ($token->isExpire()) {
            throw new \RuntimeException('Token expiré.');
        }

        if ($token->getUtilisateur()->getId() !== $user->getId()) {
            throw new \RuntimeException('Token appartenant à un autre utilisateur.');
        }

        $token->setConsomme(true);
        $this->em->flush();

        return $this->create($user, $token->getFamilleId());
    }

    public function revokeFamily(string $familleId): void
    {
        foreach ($this->repo->findBy(['familleId' => $familleId]) as $t) {
            $t->setRevoque(true);
        }
        $this->em->flush();
    }

    public function revokeAllForUser(User $user): void
    {
        foreach ($this->repo->findBy(['utilisateur' => $user, 'revoque' => false]) as $t) {
            $t->setRevoque(true);
        }
        $this->em->flush();
    }
}
