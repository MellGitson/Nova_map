<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\LienComposant;
use App\Entity\Projet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LienComposant>
 */
class LienComposantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LienComposant::class);
    }

    /** Retourne tous les liens dont la source appartient au projet donné. */
    public function findByProjet(Projet $projet): array
    {
        return $this->createQueryBuilder('l')
            ->join('l.source', 's')
            ->where('s.projet = :projet')
            ->setParameter('projet', $projet)
            ->getQuery()
            ->getResult();
    }
}
