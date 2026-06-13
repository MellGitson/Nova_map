<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * Vérifie si un bateau est déjà réservé (confirmé) sur une période donnée.
     * Utilisé avant de créer une nouvelle réservation.
     */
    public function estDisponible(string $bateauId, \DateTimeImmutable $debut, \DateTimeImmutable $fin, ?string $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.bateau = :bid')
            ->andWhere('r.statut = :statut')
            ->andWhere('r.dateDebut < :fin AND r.dateFin > :debut')
            ->setParameter('bid', $bateauId)
            ->setParameter('statut', Reservation::STATUT_CONFIRMEE)
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin);

        if ($excludeId) {
            $qb->andWhere('r.id != :eid')->setParameter('eid', $excludeId);
        }

        return $qb->getQuery()->getResult() === [];
    }

    /** @return Reservation[] */
    public function findByLocataire(string $userId): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.locataire = :uid')
            ->setParameter('uid', $userId)
            ->orderBy('r.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
