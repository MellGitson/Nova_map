<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\JournalAudit;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service de traçabilité (NIS2 + RGPD).
 * INSERT ONLY — aucune modification ni suppression des logs.
 * L'IP est hachée SHA-256 avant stockage.
 */
class AuditService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RequestStack           $requestStack,
    ) {}

    public function log(
        string  $action,
        string  $entite,
        ?string $entiteId = null,
        string  $niveau   = JournalAudit::NIVEAU_INFO,
        ?array  $donnees  = null,
        ?User   $user     = null,
    ): void {
        $request = $this->requestStack->getCurrentRequest();
        $ip      = $request?->getClientIp() ?? '0.0.0.0';

        $entry = new JournalAudit();
        $entry->setAction($action)
              ->setEntite($entite)
              ->setEntiteId($entiteId)
              ->setNiveau($niveau)
              ->setIpHash(hash('sha256', $ip))
              ->setUserAgent($request?->headers->get('User-Agent'))
              ->setDonnees($donnees)
              ->setUtilisateur($user);

        $this->em->persist($entry);
        $this->em->flush();
    }
}
