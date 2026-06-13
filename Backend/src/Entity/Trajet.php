<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TrajetRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TrajetRepository::class)]
#[ORM\Table(name: 'trajet')]
#[ORM\Index(columns: ['date_depart'], name: 'idx_trajet_date')]
#[ORM\HasLifecycleCallbacks]
class Trajet
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Bateau::class, inversedBy: 'trajets')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Bateau $bateau;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $capitaine;

    #[ORM\Column(type: 'string', length: 150, nullable: true)]
    private ?string $portDepart = null;

    #[ORM\Column(type: 'string', length: 150, nullable: true)]
    private ?string $portArrivee = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\NotBlank]
    private \DateTimeImmutable $dateDepart;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateArrivee = null;

    #[ORM\Column(type: 'decimal', precision: 8, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?float $distanceMilles = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\PositiveOrZero]
    private ?int $nombrePassagers = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $conditionsMeteo = null;

    #[ORM\Column(type: 'boolean')]
    private bool $estWeekend = false;

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

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function detecterWeekend(): void
    {
        $jour = (int) $this->dateDepart->format('N');
        $this->estWeekend = $jour >= 6;
    }

    public function getId(): string { return $this->id; }

    public function getBateau(): Bateau { return $this->bateau; }
    public function setBateau(Bateau $b): static { $this->bateau = $b; return $this; }

    public function getCapitaine(): User { return $this->capitaine; }
    public function setCapitaine(User $u): static { $this->capitaine = $u; return $this; }

    public function getPortDepart(): ?string { return $this->portDepart; }
    public function setPortDepart(?string $p): static { $this->portDepart = $p; return $this; }

    public function getPortArrivee(): ?string { return $this->portArrivee; }
    public function setPortArrivee(?string $p): static { $this->portArrivee = $p; return $this; }

    public function getDateDepart(): \DateTimeImmutable { return $this->dateDepart; }
    public function setDateDepart(\DateTimeImmutable $d): static { $this->dateDepart = $d; return $this; }

    public function getDateArrivee(): ?\DateTimeImmutable { return $this->dateArrivee; }
    public function setDateArrivee(?\DateTimeImmutable $d): static { $this->dateArrivee = $d; return $this; }

    public function getDistanceMilles(): ?float { return $this->distanceMilles; }
    public function setDistanceMilles(?float $d): static { $this->distanceMilles = $d; return $this; }

    public function getNombrePassagers(): ?int { return $this->nombrePassagers; }
    public function setNombrePassagers(?int $n): static { $this->nombrePassagers = $n; return $this; }

    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $n): static { $this->notes = $n; return $this; }

    public function getConditionsMeteo(): ?array { return $this->conditionsMeteo; }
    public function setConditionsMeteo(?array $c): static { $this->conditionsMeteo = $c; return $this; }

    public function isEstWeekend(): bool { return $this->estWeekend; }

    public function getDureeHeures(): ?float
    {
        if (!$this->dateArrivee) {
            return null;
        }
        return round($this->dateDepart->diff($this->dateArrivee)->h + $this->dateDepart->diff($this->dateArrivee)->days * 24, 1);
    }

    public function getDateCreation(): \DateTimeInterface { return $this->dateCreation; }
    public function getDateModification(): \DateTimeInterface { return $this->dateModification; }
}
