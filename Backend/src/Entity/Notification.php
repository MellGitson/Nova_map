<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\Table(name: 'notification')]
#[ORM\Index(columns: ['utilisateur_id'], name: 'idx_notif_user')]
class Notification
{
    public const STATUT_NON_LUE = 'non_lue';
    public const STATUT_LUE     = 'lue';
    public const STATUT_RESOLUE = 'resolue';

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $utilisateur;

    #[ORM\ManyToOne(targetEntity: Alerte::class, inversedBy: 'notifications')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Alerte $alerte;

    #[ORM\Column(type: 'string', length: 20, columnDefinition: "ENUM('non_lue','lue','resolue') NOT NULL DEFAULT 'non_lue'")]
    private string $statut = self::STATUT_NON_LUE;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeInterface $lueLe = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeInterface $dateCreation;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
    }

    public function getId(): string { return $this->id; }

    public function getUtilisateur(): User { return $this->utilisateur; }
    public function setUtilisateur(User $u): static { $this->utilisateur = $u; return $this; }

    public function getAlerte(): Alerte { return $this->alerte; }
    public function setAlerte(Alerte $a): static { $this->alerte = $a; return $this; }

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $s): static { $this->statut = $s; return $this; }

    public function getLueLe(): ?\DateTimeInterface { return $this->lueLe; }
    public function setLueLe(?\DateTimeInterface $d): static { $this->lueLe = $d; return $this; }

    public function getDateCreation(): \DateTimeInterface { return $this->dateCreation; }
}
