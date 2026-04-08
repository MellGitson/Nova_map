<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProjetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjetRepository::class)]
#[ORM\Table(name: 'projet')]
#[ORM\Index(columns: ['score_global'], name: 'idx_score_global')]
#[ORM\HasLifecycleCallbacks]
class Projet
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\Column(type: 'string', length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    private string $nom;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: Organisation::class, inversedBy: 'projets')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Organisation $organisation;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'projets')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $createur;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?float $scoreGlobal = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $dateCreation;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $dateModification;

    #[ORM\OneToMany(mappedBy: 'projet', targetEntity: Composant::class, cascade: ['remove'])]
    private Collection $composants;

    #[ORM\OneToMany(mappedBy: 'projet', targetEntity: Alerte::class, cascade: ['remove'])]
    private Collection $alertes;

    #[ORM\OneToMany(mappedBy: 'projet', targetEntity: SnapshotScore::class, cascade: ['remove'])]
    private Collection $snapshots;

    #[ORM\OneToMany(mappedBy: 'projet', targetEntity: Rapport::class, cascade: ['remove'])]
    private Collection $rapports;

    public function __construct()
    {
        $this->dateCreation     = new \DateTimeImmutable();
        $this->dateModification = new \DateTimeImmutable();
        $this->composants       = new ArrayCollection();
        $this->alertes          = new ArrayCollection();
        $this->snapshots        = new ArrayCollection();
        $this->rapports         = new ArrayCollection();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->dateModification = new \DateTimeImmutable();
    }

    public function getId(): string { return $this->id; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $d): static { $this->description = $d; return $this; }

    public function getOrganisation(): Organisation { return $this->organisation; }
    public function setOrganisation(Organisation $o): static { $this->organisation = $o; return $this; }

    public function getCreateur(): User { return $this->createur; }
    public function setCreateur(User $u): static { $this->createur = $u; return $this; }

    public function getScoreGlobal(): ?float { return $this->scoreGlobal; }
    public function setScoreGlobal(?float $s): static { $this->scoreGlobal = $s; return $this; }

    public function getDateCreation(): \DateTimeInterface { return $this->dateCreation; }
    public function getDateModification(): \DateTimeInterface { return $this->dateModification; }

    public function getComposants(): Collection { return $this->composants; }
    public function getAlertes(): Collection { return $this->alertes; }
    public function getSnapshots(): Collection { return $this->snapshots; }
    public function getRapports(): Collection { return $this->rapports; }
}
