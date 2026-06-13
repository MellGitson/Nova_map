<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Trajet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trajet>
 */
class TrajetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trajet::class);
    }

    /** @return Trajet[] Trajets d'un capitaine, du plus récent au plus ancien */
    public function findByCapitaine(string $userId): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.capitaine = :uid')
            ->setParameter('uid', $userId)
            ->orderBy('t.dateDepart', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** Compte les trajets weekend d'un utilisateur (pour le moteur de recommandation). */
    public function countTrajetsWeekend(string $userId): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.capitaine = :uid')
            ->andWhere('t.estWeekend = true')
            ->setParameter('uid', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** Trajets des 30 derniers jours pour un utilisateur. */
    public function findRecentsByCapitaine(string $userId, int $jours = 30): array
    {
        $depuis = new \DateTimeImmutable("-{$jours} days");
        return $this->createQueryBuilder('t')
            ->where('t.capitaine = :uid')
            ->andWhere('t.dateDepart >= :depuis')
            ->setParameter('uid', $userId)
            ->setParameter('depuis', $depuis)
            ->orderBy('t.dateDepart', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
