<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PreferenceAlerte;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PreferenceAlerte>
 */
class PreferenceAlerteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PreferenceAlerte::class);
    }
}
