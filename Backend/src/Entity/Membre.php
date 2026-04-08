<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\MembreRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MembreRepository::class)]
#[ORM\Table(name: 'membre')]
#[ORM\UniqueConstraint(name: 'UNIQ_USER_ORG', columns: ['utilisateur_id', 'organisation_id'])]
class Membre
{
    public const ROLE_USER    = 'user';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_ADMIN   = 'admin';

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'memberships')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $utilisateur;

    #[ORM\ManyToOne(targetEntity: Organisation::class, inversedBy: 'membres')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Organisation $organisation;

    #[ORM\Column(type: 'string', length: 20, columnDefinition: "ENUM('user','manager','admin') NOT NULL DEFAULT 'user'")]
    private string $role = self::ROLE_USER;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeInterface $dateAjout;

    public function __construct()
    {
        $this->dateAjout = new \DateTimeImmutable();
    }

    public function getId(): string { return $this->id; }

    public function getUtilisateur(): User { return $this->utilisateur; }
    public function setUtilisateur(User $u): static { $this->utilisateur = $u; return $this; }

    public function getOrganisation(): Organisation { return $this->organisation; }
    public function setOrganisation(Organisation $o): static { $this->organisation = $o; return $this; }

    public function getRole(): string { return $this->role; }
    public function setRole(string $r): static { $this->role = $r; return $this; }

    public function getDateAjout(): \DateTimeInterface { return $this->dateAjout; }
}
