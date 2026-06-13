<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ORM\Table(name: 'reservation')]
#[ORM\Index(columns: ['statut'], name: 'idx_resa_statut')]
#[ORM\Index(columns: ['date_debut', 'date_fin'], name: 'idx_resa_dates')]
#[ORM\HasLifecycleCallbacks]
class Reservation
{
    public const STATUT_EN_ATTENTE  = 'EN_ATTENTE';
    public const STATUT_CONFIRMEE   = 'CONFIRMEE';
    public const STATUT_ANNULEE     = 'ANNULEE';
    public const STATUT_TERMINEE    = 'TERMINEE';

    public const STATUTS = [
        self::STATUT_EN_ATTENTE,
        self::STATUT_CONFIRMEE,
        self::STATUT_ANNULEE,
        self::STATUT_TERMINEE,
    ];

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Bateau::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Bateau $bateau;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $locataire;

    #[ORM\Column(type: 'date_immutable')]
    #[Assert\NotBlank]
    private \DateTimeImmutable $dateDebut;

    #[ORM\Column(type: 'date_immutable')]
    #[Assert\NotBlank]
    #[Assert\GreaterThan(propertyPath: 'dateDebut', message: 'La date de fin doit être après la date de début.')]
    private \DateTimeImmutable $dateFin;

    #[ORM\Column(type: 'string', length: 20)]
    #[Assert\Choice(choices: self::STATUTS)]
    private string $statut = self::STATUT_EN_ATTENTE;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $montantTotal = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeInterface $dateCreation;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeInterface $dateModification;

    public function __construct()
    {
        $this->dateCreation     = new \DateTimeImmutable();
        $this->dateModification = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->dateModification = new \DateTimeImmutable();
    }

    public function getId(): string { return $this->id; }

    public function getBateau(): Bateau { return $this->bateau; }
    public function setBateau(Bateau $b): static { $this->bateau = $b; return $this; }

    public function getLocataire(): User { return $this->locataire; }
    public function setLocataire(User $u): static { $this->locataire = $u; return $this; }

    public function getDateDebut(): \DateTimeImmutable { return $this->dateDebut; }
    public function setDateDebut(\DateTimeImmutable $d): static { $this->dateDebut = $d; return $this; }

    public function getDateFin(): \DateTimeImmutable { return $this->dateFin; }
    public function setDateFin(\DateTimeImmutable $d): static { $this->dateFin = $d; return $this; }

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $s): static { $this->statut = $s; return $this; }

    public function getMontantTotal(): ?float { return $this->montantTotal; }
    public function setMontantTotal(?float $m): static { $this->montantTotal = $m; return $this; }

    public function getCommentaire(): ?string { return $this->commentaire; }
    public function setCommentaire(?string $c): static { $this->commentaire = $c; return $this; }

    public function getDateCreation(): \DateTimeInterface { return $this->dateCreation; }
    public function getDateModification(): \DateTimeInterface { return $this->dateModification; }

    public function getNombreJours(): int
    {
        return $this->dateDebut->diff($this->dateFin)->days;
    }

    public function calculerMontant(): void
    {
        $prixJour = $this->bateau->getPrixParJour();
        if ($prixJour !== null) {
            $this->montantTotal = $this->getNombreJours() * $prixJour;
        }
    }
}
