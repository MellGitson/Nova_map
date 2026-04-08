<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RapportRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RapportRepository::class)]
#[ORM\Table(name: 'rapport')]
class Rapport
{
    public const TYPE_AUDIT      = 'audit';
    public const TYPE_CONFORMITE = 'conformite';

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Projet::class, inversedBy: 'rapports')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Projet $projet;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'generateur_id', nullable: false, onDelete: 'CASCADE')]
    private User $generateur;

    #[ORM\Column(type: 'string', length: 20, columnDefinition: "ENUM('audit','conformite') NOT NULL DEFAULT 'audit'")]
    private string $type = self::TYPE_AUDIT;

    /** Chemin relatif hors webroot */
    #[ORM\Column(type: 'string', length: 500)]
    private string $cheminFichier;

    /** Snapshot JSON des données au moment de la génération */
    #[ORM\Column(type: 'json')]
    private array $donneesSnapshot = [];

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $dateCreation;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
    }

    public function getId(): string { return $this->id; }

    public function getProjet(): Projet { return $this->projet; }
    public function setProjet(Projet $p): static { $this->projet = $p; return $this; }

    public function getGenerateur(): User { return $this->generateur; }
    public function setGenerateur(User $u): static { $this->generateur = $u; return $this; }

    public function getType(): string { return $this->type; }
    public function setType(string $t): static { $this->type = $t; return $this; }

    public function getCheminFichier(): string { return $this->cheminFichier; }
    public function setCheminFichier(string $c): static { $this->cheminFichier = $c; return $this; }

    public function getDonneesSnapshot(): array { return $this->donneesSnapshot; }
    public function setDonneesSnapshot(array $d): static { $this->donneesSnapshot = $d; return $this; }

    public function getDateCreation(): \DateTimeInterface { return $this->dateCreation; }
}
