<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\RefreshToken;
use App\Entity\User;
use App\Repository\RefreshTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Token Family Pattern (CDCF §4.1).
 * - Chaque famille a un UUID partagé.
 * - Un token refresh ne peut être utilisé qu'une seule fois (consomme=true).
 * - Si un token déjà consommé est réutilisé → toute la famille est révoquée (CRITICAL).
 */
class TokenFamilyService
{
    public function __construct(
        private readonly EntityManagerInterface  $em,
        private readonly RefreshTokenRepository  $repo,
        private readonly AuditService            $audit,
        private readonly int                     $ttlSeconds = 604800, // 7 jours
    ) {}

    /** Crée un nouveau refresh token pour un utilisateur dans une nouvelle famille. */
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
     * Valide et consomme un refresh token.
     *
     * @throws \RuntimeException Si le token est invalide ou la famille compromise.
     */
    public function consume(string $rawToken, User $user): RefreshToken
    {
        $token = $this->repo->findOneBy(['token' => $rawToken]);

        if (!$token) {
            throw new \RuntimeException('Token introuvable.');
        }

        // Détection de réutilisation → révocation de toute la famille (CRITICAL)
        if ($token->isConsomme()) {
            $this->revokeFamily($token->getFamilleId());
            $this->audit->log(
                action: 'REFRESH_TOKEN_REUSE',
                entite: 'refresh_token',
                entiteId: $token->getId(),
                niveau: \App\Entity\JournalAudit::NIVEAU_CRITICAL,
                donnees: ['famille_id' => $token->getFamilleId()],
                user: $user,
            );
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

        // Marquer comme consommé
        $token->setConsomme(true);
        $this->em->flush();

        // Créer un nouveau token dans la même famille
        return $this->create($user, $token->getFamilleId());
    }

    /** Révoque tous les tokens d'une famille. */
    public function revokeFamily(string $familleId): void
    {
        $tokens = $this->repo->findBy(['familleId' => $familleId]);
        foreach ($tokens as $t) {
            $t->setRevoque(true);
        }
        $this->em->flush();
    }

    /** Révoque tous les tokens actifs d'un utilisateur (déconnexion globale). */
    public function revokeAllForUser(User $user): void
    {
        $tokens = $this->repo->findBy(['utilisateur' => $user, 'revoque' => false]);
        foreach ($tokens as $t) {
            $t->setRevoque(true);
        }
        $this->em->flush();
    }
}
