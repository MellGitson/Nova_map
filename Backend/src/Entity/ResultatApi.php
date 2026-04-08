<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ResultatApiRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResultatApiRepository::class)]
#[ORM\Table(name: 'resultat_api')]
class ResultatApi
{
    public const SOURCE_SHODAN     = 'shodan';
    public const SOURCE_NVD        = 'nvd';
    public const SOURCE_SSLLABS    = 'ssllabs';
    public const SOURCE_SECHEADERS = 'secheaders';
    public const SOURCE_IPINFO     = 'ipinfo';

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Analyse::class, inversedBy: 'resultatsApi')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Analyse $analyse;

    #[ORM\Column(type: 'string', length: 20, columnDefinition: "ENUM('shodan','nvd','ssllabs','secheaders','ipinfo') NOT NULL")]
    private string $apiSource;

    #[ORM\Column(type: 'json')]
    private array $donneesBrutes = [];

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    private float $penalite = 0.0;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $erreur = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $dateCreation;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
    }

    public function getId(): string { return $this->id; }

    public function getAnalyse(): Analyse { return $this->analyse; }
    public function setAnalyse(Analyse $a): static { $this->analyse = $a; return $this; }

    public function getApiSource(): string { return $this->apiSource; }
    public function setApiSource(string $s): static { $this->apiSource = $s; return $this; }

    public function getDonneesBrutes(): array { return $this->donneesBrutes; }
    public function setDonneesBrutes(array $d): static { $this->donneesBrutes = $d; return $this; }

    public function getPenalite(): float { return $this->penalite; }
    public function setPenalite(float $p): static { $this->penalite = $p; return $this; }

    public function getErreur(): ?string { return $this->erreur; }
    public function setErreur(?string $e): static { $this->erreur = $e; return $this; }

    public function getDateCreation(): \DateTimeInterface { return $this->dateCreation; }
}
