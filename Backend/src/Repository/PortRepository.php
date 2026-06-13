<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Port;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Port>
 */
class PortRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Port::class);
    }

    /** Ports dans une bounding box, avec comptage de bateaux disponibles. */
    public function findActifsDansBbox(float $latMin, float $latMax, float $lngMin, float $lngMax): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.actif = true')
            ->andWhere('p.latitude BETWEEN :latMin AND :latMax')
            ->andWhere('p.longitude BETWEEN :lngMin AND :lngMax')
            ->setParameter('latMin', $latMin)
            ->setParameter('latMax', $latMax)
            ->setParameter('lngMin', $lngMin)
            ->setParameter('lngMax', $lngMax)
            ->getQuery()
            ->getResult();
    }
}
