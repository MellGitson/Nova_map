<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ComposantCveRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ComposantCveRepository::class)]
#[ORM\Table(name: 'composant_cve')]
#[ORM\UniqueConstraint(name: 'UNIQ_COMPOSANT_CVE', columns: ['composant_id', 'cve_id'])]
class ComposantCve
{
    public const STATUT_ACTIVE  = 'active';
    public const STATUT_RESOLUE = 'resolue';
    public const STATUT_IGNOREE = 'ignoree';

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Composant::class, inversedBy: 'composantCves')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Composant $composant;

    #[ORM\ManyToOne(targetEntity: Cve::class, inversedBy: 'composantCves')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Cve $cve;

    #[ORM\Column(type: 'string', length: 20, columnDefinition: "ENUM('active','resolue','ignoree') NOT NULL DEFAULT 'active'")]
    private string $statut = self::STATUT_ACTIVE;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeInterface $detecteLe;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeInterface $resoluLe = null;

    public function __construct()
    {
        $this->detecteLe = new \DateTimeImmutable();
    }

    public function getId(): string { return $this->id; }

    public function getComposant(): Composant { return $this->composant; }
    public function setComposant(Composant $c): static { $this->composant = $c; return $this; }

    public function getCve(): Cve { return $this->cve; }
    public function setCve(Cve $c): static { $this->cve = $c; return $this; }

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $s): static { $this->statut = $s; return $this; }

    public function getDetecteLe(): \DateTimeInterface { return $this->detecteLe; }
    public function getResoluLe(): ?\DateTimeInterface { return $this->resoluLe; }
    public function setResoluLe(?\DateTimeInterface $d): static { $this->resoluLe = $d; return $this; }
}
