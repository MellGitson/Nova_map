<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Bateau;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Bateau>
 */
class BateauRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bateau::class);
    }

    /**
     * Filtre les bateaux disponibles dans une bounding box géographique (via leur port).
     * Optimisé pour la carte Leaflet : seuls les ports dans la zone visible sont chargés.
     */
    public function findDisponiblesDansBbox(float $latMin, float $latMax, float $lngMin, float $lngMax): array
    {
        return $this->createQueryBuilder('b')
            ->join('b.port', 'p')
            ->where('b.statut = :statut')
            ->andWhere('p.latitude BETWEEN :latMin AND :latMax')
            ->andWhere('p.longitude BETWEEN :lngMin AND :lngMax')
            ->setParameter('statut', Bateau::STATUT_DISPONIBLE)
            ->setParameter('latMin', $latMin)
            ->setParameter('latMax', $latMax)
            ->setParameter('lngMin', $lngMin)
            ->setParameter('lngMax', $lngMax)
            ->getQuery()
            ->getResult();
    }

    /** @return Bateau[] */
    public function findByProprietaire(string $userId): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.proprietaire = :uid')
            ->setParameter('uid', $userId)
            ->orderBy('b.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
