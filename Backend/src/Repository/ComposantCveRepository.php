<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ComposantCve;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ComposantCve>
 */
class ComposantCveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ComposantCve::class);
    }
}
