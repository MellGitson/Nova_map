<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PreferenceAlerteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PreferenceAlerteRepository::class)]
#[ORM\Table(name: 'preference_alerte')]
class PreferenceAlerte
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, unique: true, onDelete: 'CASCADE')]
    private User $utilisateur;

    #[ORM\Column(type: 'boolean')]
    private bool $emailActive = true;

    /** Score en dessous duquel une alerte est déclenchée */
    #[ORM\Column(type: 'integer')]
    private int $seuilScore = 50;

    public function getId(): string { return $this->id; }

    public function getUtilisateur(): User { return $this->utilisateur; }
    public function setUtilisateur(User $u): static { $this->utilisateur = $u; return $this; }

    public function isEmailActive(): bool { return $this->emailActive; }
    public function setEmailActive(bool $v): static { $this->emailActive = $v; return $this; }

    public function getSeuilScore(): int { return $this->seuilScore; }
    public function setSeuilScore(int $s): static { $this->seuilScore = $s; return $this; }
}
