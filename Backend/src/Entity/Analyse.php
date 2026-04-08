<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AnalyseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnalyseRepository::class)]
#[ORM\Table(name: 'analyse')]
#[ORM\Index(columns: ['composant_id'], name: 'idx_composant')]
class Analyse
{
    public const DECLENCHEUR_MANUEL = 'manuel';
    public const DECLENCHEUR_CRON   = 'cron';
    public const DECLENCHEUR_ALERTE = 'alerte';

    public const STATUT_EN_COURS = 'en_cours';
    public const STATUT_TERMINE  = 'termine';
    public const STATUT_ERREUR   = 'erreur';

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Composant::class, inversedBy: 'analyses')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Composant $composant;

    #[ORM\Column(type: 'string', length: 20, columnDefinition: "ENUM('manuel','cron','alerte') NOT NULL DEFAULT 'manuel'")]
    private string $declencheur = self::DECLENCHEUR_MANUEL;

    #[ORM\Column(type: 'string', length: 20, columnDefinition: "ENUM('en_cours','termine','erreur') NOT NULL DEFAULT 'en_cours'")]
    private string $statut = self::STATUT_EN_COURS;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?float $scoreAvant = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?float $scoreApres = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeInterface $dateDebut;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\OneToMany(mappedBy: 'analyse', targetEntity: ResultatApi::class, cascade: ['remove'])]
    private Collection $resultatsApi;

    public function __construct()
    {
        $this->dateDebut    = new \DateTimeImmutable();
        $this->resultatsApi = new ArrayCollection();
    }

    public function getId(): string { return $this->id; }

    public function getComposant(): Composant { return $this->composant; }
    public function setComposant(Composant $c): static { $this->composant = $c; return $this; }

    public function getDeclencheur(): string { return $this->declencheur; }
    public function setDeclencheur(string $d): static { $this->declencheur = $d; return $this; }

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $s): static { $this->statut = $s; return $this; }

    public function getScoreAvant(): ?float { return $this->scoreAvant; }
    public function setScoreAvant(?float $s): static { $this->scoreAvant = $s; return $this; }

    public function getScoreApres(): ?float { return $this->scoreApres; }
    public function setScoreApres(?float $s): static { $this->scoreApres = $s; return $this; }

    public function getDateDebut(): \DateTimeInterface { return $this->dateDebut; }
    public function getDateFin(): ?\DateTimeInterface { return $this->dateFin; }
    public function setDateFin(?\DateTimeInterface $d): static { $this->dateFin = $d; return $this; }

    public function getResultatsApi(): Collection { return $this->resultatsApi; }
}
