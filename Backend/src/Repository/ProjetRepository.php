<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Projet;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Projet>
 */
class ProjetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Projet::class);
    }

    /** Retourne tous les projets accessibles à un utilisateur (créateur OU membre de l'org). */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.organisation', 'o')
            ->leftJoin('o.membres', 'm')
            ->where('p.createur = :user')
            ->orWhere('m.utilisateur = :user')
            ->setParameter('user', $user)
            ->orderBy('p.dateModification', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
