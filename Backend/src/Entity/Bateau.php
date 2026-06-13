<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BateauRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BateauRepository::class)]
#[ORM\Table(name: 'bateau')]
#[ORM\Index(columns: ['statut'], name: 'idx_statut')]
#[ORM\HasLifecycleCallbacks]
class Bateau
{
    public const STATUT_DISPONIBLE   = 'DISPONIBLE';
    public const STATUT_LOUE         = 'LOUE';
    public const STATUT_EN_REPARATION = 'EN_REPARATION';

    public const STATUTS = [
        self::STATUT_DISPONIBLE,
        self::STATUT_LOUE,
        self::STATUT_EN_REPARATION,
    ];

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\Column(type: 'string', length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    private string $nom;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $marque = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\Positive]
    private ?int $annee = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    #[Assert\Positive]
    private ?float $longueur = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\Positive]
    private ?int $capacitePersonnes = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?float $prixParJour = null;

    #[ORM\Column(type: 'string', length: 20)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::STATUTS)]
    private string $statut = self::STATUT_DISPONIBLE;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $photos = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'bateaux')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $proprietaire;

    #[ORM\ManyToOne(targetEntity: Port::class, inversedBy: 'bateaux')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Port $port = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeInterface $dateCreation;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeInterface $dateModification;

    #[ORM\OneToMany(mappedBy: 'bateau', targetEntity: Reservation::class, cascade: ['remove'])]
    private Collection $reservations;

    #[ORM\OneToMany(mappedBy: 'bateau', targetEntity: Trajet::class, cascade: ['remove'])]
    private Collection $trajets;

    public function __construct()
    {
        $this->reservations     = new ArrayCollection();
        $this->trajets          = new ArrayCollection();
        $this->dateCreation     = new \DateTimeImmutable();
        $this->dateModification = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->dateModification = new \DateTimeImmutable();
    }

    public function getId(): string { return $this->id; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getType(): ?string { return $this->type; }
    public function setType(?string $t): static { $this->type = $t; return $this; }

    public function getMarque(): ?string { return $this->marque; }
    public function setMarque(?string $m): static { $this->marque = $m; return $this; }

    public function getAnnee(): ?int { return $this->annee; }
    public function setAnnee(?int $a): static { $this->annee = $a; return $this; }

    public function getLongueur(): ?float { return $this->longueur; }
    public function setLongueur(?float $l): static { $this->longueur = $l; return $this; }

    public function getCapacitePersonnes(): ?int { return $this->capacitePersonnes; }
    public function setCapacitePersonnes(?int $c): static { $this->capacitePersonnes = $c; return $this; }

    public function getPrixParJour(): ?float { return $this->prixParJour; }
    public function setPrixParJour(?float $p): static { $this->prixParJour = $p; return $this; }

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $s): static { $this->statut = $s; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $d): static { $this->description = $d; return $this; }

    public function getPhotos(): ?array { return $this->photos; }
    public function setPhotos(?array $p): static { $this->photos = $p; return $this; }

    public function getProprietaire(): User { return $this->proprietaire; }
    public function setProprietaire(User $u): static { $this->proprietaire = $u; return $this; }

    public function getPort(): ?Port { return $this->port; }
    public function setPort(?Port $p): static { $this->port = $p; return $this; }

    public function getDateCreation(): \DateTimeInterface { return $this->dateCreation; }
    public function getDateModification(): \DateTimeInterface { return $this->dateModification; }

    public function getReservations(): Collection { return $this->reservations; }
    public function getTrajets(): Collection { return $this->trajets; }

    public function isDisponible(): bool { return $this->statut === self::STATUT_DISPONIBLE; }
}
