<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ComposantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ComposantRepository::class)]
#[ORM\Table(name: 'composant')]
#[ORM\Index(columns: ['projet_id'], name: 'idx_projet')]
#[ORM\Index(columns: ['score'], name: 'idx_score')]
class Composant
{
    public const TYPE_SERVEUR = 'serveur';
    public const TYPE_BDD     = 'bdd';
    public const TYPE_API     = 'api';
    public const TYPE_CDN     = 'cdn';
    public const TYPE_CLOUD   = 'cloud';

    public const ENV_PROD    = 'prod';
    public const ENV_STAGING = 'staging';
    public const ENV_DEV     = 'dev';

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Projet::class, inversedBy: 'composants')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Projet $projet;

    #[ORM\Column(type: 'string', length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    private string $nom;

    #[ORM\Column(type: 'string', length: 20, columnDefinition: "ENUM('serveur','bdd','api','cdn','cloud') NOT NULL")]
    private string $type = self::TYPE_SERVEUR;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $ipOuDomaine = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $versionLogicielle = null;

    #[ORM\Column(type: 'string', length: 20, columnDefinition: "ENUM('prod','staging','dev') NOT NULL DEFAULT 'prod'")]
    private string $environnement = self::ENV_PROD;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $port = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?float $score = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeInterface $derniereAnalyse = null;

    #[ORM\Column(type: 'float')]
    private float $positionX = 0.0;

    #[ORM\Column(type: 'float')]
    private float $positionY = 0.0;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeInterface $dateCreation;

    #[ORM\OneToMany(mappedBy: 'source', targetEntity: LienComposant::class, cascade: ['remove'])]
    private Collection $liensSource;

    #[ORM\OneToMany(mappedBy: 'cible', targetEntity: LienComposant::class, cascade: ['remove'])]
    private Collection $liensCible;

    #[ORM\OneToMany(mappedBy: 'composant', targetEntity: Analyse::class, cascade: ['remove'])]
    private Collection $analyses;

    #[ORM\OneToMany(mappedBy: 'composant', targetEntity: ComposantCve::class, cascade: ['remove'])]
    private Collection $composantCves;

    public function __construct()
    {
        $this->dateCreation  = new \DateTimeImmutable();
        $this->liensSource   = new ArrayCollection();
        $this->liensCible    = new ArrayCollection();
        $this->analyses      = new ArrayCollection();
        $this->composantCves = new ArrayCollection();
    }

    public function getId(): string { return $this->id; }

    public function getProjet(): Projet { return $this->projet; }
    public function setProjet(Projet $p): static { $this->projet = $p; return $this; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $n): static { $this->nom = $n; return $this; }

    public function getType(): string { return $this->type; }
    public function setType(string $t): static { $this->type = $t; return $this; }

    public function getIpOuDomaine(): ?string { return $this->ipOuDomaine; }
    public function setIpOuDomaine(?string $v): static { $this->ipOuDomaine = $v; return $this; }

    public function getVersionLogicielle(): ?string { return $this->versionLogicielle; }
    public function setVersionLogicielle(?string $v): static { $this->versionLogicielle = $v; return $this; }

    public function getEnvironnement(): string { return $this->environnement; }
    public function setEnvironnement(string $e): static { $this->environnement = $e; return $this; }

    public function getPort(): ?int { return $this->port; }
    public function setPort(?int $p): static { $this->port = $p; return $this; }

    public function getScore(): ?float { return $this->score; }
    public function setScore(?float $s): static { $this->score = $s; return $this; }

    public function getDerniereAnalyse(): ?\DateTimeInterface { return $this->derniereAnalyse; }
    public function setDerniereAnalyse(?\DateTimeInterface $d): static { $this->derniereAnalyse = $d; return $this; }

    public function getPositionX(): float { return $this->positionX; }
    public function setPositionX(float $x): static { $this->positionX = $x; return $this; }

    public function getPositionY(): float { return $this->positionY; }
    public function setPositionY(float $y): static { $this->positionY = $y; return $this; }

    public function getDateCreation(): \DateTimeInterface { return $this->dateCreation; }

    public function getLiensSource(): Collection { return $this->liensSource; }
    public function getLiensCible(): Collection { return $this->liensCible; }
    public function getAnalyses(): Collection { return $this->analyses; }
    public function getComposantCves(): Collection { return $this->composantCves; }
}
