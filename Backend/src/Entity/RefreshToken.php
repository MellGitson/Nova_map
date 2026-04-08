<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RefreshTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RefreshTokenRepository::class)]
#[ORM\Table(name: 'refresh_token')]
#[ORM\Index(columns: ['famille_id'], name: 'idx_famille')]
#[ORM\Index(columns: ['token'], name: 'idx_token')]
class RefreshToken
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'refreshTokens')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $utilisateur;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $token;

    /** UUID partagé par tous les tokens d'une même famille (token family pattern) */
    #[ORM\Column(type: 'string', length: 36)]
    private string $familleId;

    #[ORM\Column(type: 'boolean')]
    private bool $consomme = false;

    #[ORM\Column(type: 'boolean')]
    private bool $revoque = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeInterface $expireA;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeInterface $dateCreation;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
    }

    public function getId(): string { return $this->id; }

    public function getUtilisateur(): User { return $this->utilisateur; }
    public function setUtilisateur(User $u): static { $this->utilisateur = $u; return $this; }

    public function getToken(): string { return $this->token; }
    public function setToken(string $t): static { $this->token = $t; return $this; }

    public function getFamilleId(): string { return $this->familleId; }
    public function setFamilleId(string $f): static { $this->familleId = $f; return $this; }

    public function isConsomme(): bool { return $this->consomme; }
    public function setConsomme(bool $v): static { $this->consomme = $v; return $this; }

    public function isRevoque(): bool { return $this->revoque; }
    public function setRevoque(bool $v): static { $this->revoque = $v; return $this; }

    public function getExpireA(): \DateTimeInterface { return $this->expireA; }
    public function setExpireA(\DateTimeInterface $d): static { $this->expireA = $d; return $this; }

    public function isExpire(): bool { return $this->expireA < new \DateTimeImmutable(); }
    public function isValide(): bool { return !$this->consomme && !$this->revoque && !$this->isExpire(); }

    public function getDateCreation(): \DateTimeInterface { return $this->dateCreation; }
}
