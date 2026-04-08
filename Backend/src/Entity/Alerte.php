<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AlerteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AlerteRepository::class)]
#[ORM\Table(name: 'alerte')]
#[ORM\Index(columns: ['projet_id', 'severite', 'date_creation'], name: 'idx_alerte_projet')]
class Alerte
{
    public const TYPE_CVE_CRITIQUE  = 'cve_critique';
    public const TYPE_SCORE_BAISSE  = 'score_baisse';
    public const TYPE_PORT_EXPOSE   = 'port_expose';
    public const TYPE_SSL_EXPIRE    = 'ssl_expire';

    public const SEVERITE_CRITIQUE = 'critique';
    public const SEVERITE_WARNING  = 'warning';
    public const SEVERITE_INFO     = 'info';

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Projet::class, inversedBy: 'alertes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Projet $projet;

    #[ORM\ManyToOne(targetEntity: Composant::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Composant $composant = null;

    #[ORM\Column(type: 'string', length: 30, columnDefinition: "ENUM('cve_critique','score_baisse','port_expose','ssl_expire') NOT NULL")]
    private string $type;

    #[ORM\Column(type: 'string', length: 20, columnDefinition: "ENUM('critique','warning','info') NOT NULL DEFAULT 'warning'")]
    private string $severite = self::SEVERITE_WARNING;

    #[ORM\Column(type: 'string', length: 255)]
    private string $titre;

    #[ORM\Column(type: 'text')]
    private string $message;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $donnees = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $dateCreation;

    #[ORM\OneToMany(mappedBy: 'alerte', targetEntity: Notification::class, cascade: ['remove'])]
    private Collection $notifications;

    public function __construct()
    {
        $this->dateCreation  = new \DateTimeImmutable();
        $this->notifications = new ArrayCollection();
    }

    public function getId(): string { return $this->id; }

    public function getProjet(): Projet { return $this->projet; }
    public function setProjet(Projet $p): static { $this->projet = $p; return $this; }

    public function getComposant(): ?Composant { return $this->composant; }
    public function setComposant(?Composant $c): static { $this->composant = $c; return $this; }

    public function getType(): string { return $this->type; }
    public function setType(string $t): static { $this->type = $t; return $this; }

    public function getSeverite(): string { return $this->severite; }
    public function setSeverite(string $s): static { $this->severite = $s; return $this; }

    public function getTitre(): string { return $this->titre; }
    public function setTitre(string $t): static { $this->titre = $t; return $this; }

    public function getMessage(): string { return $this->message; }
    public function setMessage(string $m): static { $this->message = $m; return $this; }

    public function getDonnees(): ?array { return $this->donnees; }
    public function setDonnees(?array $d): static { $this->donnees = $d; return $this; }

    public function getDateCreation(): \DateTimeInterface { return $this->dateCreation; }
    public function getNotifications(): Collection { return $this->notifications; }
}
