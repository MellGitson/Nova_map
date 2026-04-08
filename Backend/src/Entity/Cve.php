<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CveRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CveRepository::class)]
#[ORM\Table(name: 'cve')]
#[ORM\UniqueConstraint(name: 'UNIQ_CVE_ID', columns: ['cve_id'])]
#[ORM\Index(columns: ['cve_id'], name: 'idx_cve_id')]
class Cve
{
    public const SEVERITE_CRITIQUE = 'critique';
    public const SEVERITE_ELEVE    = 'eleve';
    public const SEVERITE_MOYEN    = 'moyen';
    public const SEVERITE_FAIBLE   = 'faible';

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\Column(type: 'string', length: 20, unique: true)]
    private string $cveId;

    #[ORM\Column(type: 'decimal', precision: 3, scale: 1, nullable: true)]
    private ?float $scoreCvss = null;

    #[ORM\Column(type: 'string', length: 20, columnDefinition: "ENUM('critique','eleve','moyen','faible') NOT NULL DEFAULT 'moyen'")]
    private string $severite = self::SEVERITE_MOYEN;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'boolean')]
    private bool $correctifDisponible = false;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $datePublication = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeInterface $dateCreation;

    #[ORM\OneToMany(mappedBy: 'cve', targetEntity: ComposantCve::class, cascade: ['remove'])]
    private Collection $composantCves;

    public function __construct()
    {
        $this->dateCreation  = new \DateTimeImmutable();
        $this->composantCves = new ArrayCollection();
    }

    public function getId(): string { return $this->id; }

    public function getCveId(): string { return $this->cveId; }
    public function setCveId(string $id): static { $this->cveId = $id; return $this; }

    public function getScoreCvss(): ?float { return $this->scoreCvss; }
    public function setScoreCvss(?float $s): static { $this->scoreCvss = $s; return $this; }

    public function getSeverite(): string { return $this->severite; }
    public function setSeverite(string $s): static { $this->severite = $s; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $d): static { $this->description = $d; return $this; }

    public function isCorrectifDisponible(): bool { return $this->correctifDisponible; }
    public function setCorrectifDisponible(bool $v): static { $this->correctifDisponible = $v; return $this; }

    public function getDatePublication(): ?\DateTimeInterface { return $this->datePublication; }
    public function setDatePublication(?\DateTimeInterface $d): static { $this->datePublication = $d; return $this; }

    public function getDateCreation(): \DateTimeInterface { return $this->dateCreation; }
    public function getComposantCves(): Collection { return $this->composantCves; }
}
