<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\JournalAuditRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * INSERT ONLY — Jamais modifié ni supprimé (conformité NIS2).
 * IP hachée SHA-256 côté service avant insertion (conformité RGPD).
 */
#[ORM\Entity(repositoryClass: JournalAuditRepository::class)]
#[ORM\Table(name: 'journal_audit')]
#[ORM\Index(columns: ['action'], name: 'idx_audit_action')]
#[ORM\Index(columns: ['niveau'], name: 'idx_audit_niveau')]
#[ORM\Index(columns: ['date_creation'], name: 'idx_audit_date')]
class JournalAudit
{
    public const NIVEAU_INFO     = 'info';
    public const NIVEAU_WARNING  = 'warning';
    public const NIVEAU_CRITICAL = 'critical';

    #[ORM\Id]
    #[ORM\Column(type: 'bigint')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    /** Peut être null si l'utilisateur a été anonymisé (RGPD) */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $utilisateur = null;

    #[ORM\Column(type: 'string', length: 100)]
    private string $action;

    #[ORM\Column(type: 'string', length: 50)]
    private string $entite;

    #[ORM\Column(type: 'string', length: 36, nullable: true)]
    private ?string $entiteId = null;

    #[ORM\Column(type: 'string', length: 20, columnDefinition: "ENUM('info','warning','critical') NOT NULL DEFAULT 'info'")]
    private string $niveau = self::NIVEAU_INFO;

    /** IP hachée SHA-256 (jamais l'IP en clair — conformité RGPD) */
    #[ORM\Column(type: 'string', length: 64)]
    private string $ipHash;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $donnees = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeInterface $dateCreation;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
    }

    public function getId(): int { return $this->id; }

    public function getUtilisateur(): ?User { return $this->utilisateur; }
    public function setUtilisateur(?User $u): static { $this->utilisateur = $u; return $this; }

    public function getAction(): string { return $this->action; }
    public function setAction(string $a): static { $this->action = $a; return $this; }

    public function getEntite(): string { return $this->entite; }
    public function setEntite(string $e): static { $this->entite = $e; return $this; }

    public function getEntiteId(): ?string { return $this->entiteId; }
    public function setEntiteId(?string $id): static { $this->entiteId = $id; return $this; }

    public function getNiveau(): string { return $this->niveau; }
    public function setNiveau(string $n): static { $this->niveau = $n; return $this; }

    public function getIpHash(): string { return $this->ipHash; }
    public function setIpHash(string $h): static { $this->ipHash = $h; return $this; }

    public function getUserAgent(): ?string { return $this->userAgent; }
    public function setUserAgent(?string $ua): static { $this->userAgent = $ua; return $this; }

    public function getDonnees(): ?array { return $this->donnees; }
    public function setDonnees(?array $d): static { $this->donnees = $d; return $this; }

    public function getDateCreation(): \DateTimeInterface { return $this->dateCreation; }
}
