<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PortRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PortRepository::class)]
#[ORM\Table(name: 'port')]
#[ORM\Index(columns: ['latitude', 'longitude'], name: 'idx_geo')]
#[ORM\HasLifecycleCallbacks]
class Port
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

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $pays = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 7)]
    #[Assert\NotBlank]
    #[Assert\Range(min: -90, max: 90)]
    private float $latitude;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 7)]
    #[Assert\NotBlank]
    #[Assert\Range(min: -180, max: 180)]
    private float $longitude;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\PositiveOrZero]
    private ?int $capacite = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'boolean')]
    private bool $actif = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeInterface $dateCreation;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeInterface $dateModification;

    #[ORM\OneToMany(mappedBy: 'port', targetEntity: Bateau::class)]
    private Collection $bateaux;

    public function __construct()
    {
        $this->bateaux          = new ArrayCollection();
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

    public function getVille(): ?string { return $this->ville; }
    public function setVille(?string $v): static { $this->ville = $v; return $this; }

    public function getPays(): ?string { return $this->pays; }
    public function setPays(?string $p): static { $this->pays = $p; return $this; }

    public function getLatitude(): float { return $this->latitude; }
    public function setLatitude(float $lat): static { $this->latitude = $lat; return $this; }

    public function getLongitude(): float { return $this->longitude; }
    public function setLongitude(float $lng): static { $this->longitude = $lng; return $this; }

    public function getCapacite(): ?int { return $this->capacite; }
    public function setCapacite(?int $c): static { $this->capacite = $c; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $d): static { $this->description = $d; return $this; }

    public function isActif(): bool { return $this->actif; }
    public function setActif(bool $a): static { $this->actif = $a; return $this; }

    public function getDateCreation(): \DateTimeInterface { return $this->dateCreation; }
    public function getDateModification(): \DateTimeInterface { return $this->dateModification; }

    public function getBateaux(): Collection { return $this->bateaux; }
}
