<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\WebhookRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WebhookRepository::class)]
#[ORM\Table(name: 'webhook')]
class Webhook
{
    public const SERVICE_SLACK   = 'slack';
    public const SERVICE_TEAMS   = 'teams';
    public const SERVICE_DISCORD = 'discord';
    public const SERVICE_CUSTOM  = 'custom';

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $utilisateur;

    #[ORM\Column(type: 'string', length: 500)]
    #[Assert\NotBlank]
    #[Assert\Url]
    private string $url;

    #[ORM\Column(type: 'string', length: 20, columnDefinition: "ENUM('slack','teams','discord','custom') NOT NULL")]
    private string $typeService;

    #[ORM\Column(type: 'boolean')]
    private bool $actif = true;

    /** Secret chiffré AES-256 en base */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $secret = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeInterface $dateCreation;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
    }

    public function getId(): string { return $this->id; }

    public function getUtilisateur(): User { return $this->utilisateur; }
    public function setUtilisateur(User $u): static { $this->utilisateur = $u; return $this; }

    public function getUrl(): string { return $this->url; }
    public function setUrl(string $u): static { $this->url = $u; return $this; }

    public function getTypeService(): string { return $this->typeService; }
    public function setTypeService(string $t): static { $this->typeService = $t; return $this; }

    public function isActif(): bool { return $this->actif; }
    public function setActif(bool $v): static { $this->actif = $v; return $this; }

    public function getSecret(): ?string { return $this->secret; }
    public function setSecret(?string $s): static { $this->secret = $s; return $this; }

    public function getDateCreation(): \DateTimeInterface { return $this->dateCreation; }
}
