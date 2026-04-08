<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SnapshotScoreRepository;
use Doctrine\ORM\Mapping as ORM;

/** Archivage hebdomadaire automatique (chaque lundi) du score d'un projet. */
#[ORM\Entity(repositoryClass: SnapshotScoreRepository::class)]
#[ORM\Table(name: 'snapshot_score')]
#[ORM\Index(columns: ['projet_id', 'date_snapshot'], name: 'idx_snapshot_projet')]
class SnapshotScore
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Projet::class, inversedBy: 'snapshots')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Projet $projet;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    private float $scoreGlobal;

    #[ORM\Column(type: 'integer')]
    private int $nbCveActives = 0;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $donnees = null;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $dateSnapshot;

    public function getId(): string { return $this->id; }

    public function getProjet(): Projet { return $this->projet; }
    public function setProjet(Projet $p): static { $this->projet = $p; return $this; }

    public function getScoreGlobal(): float { return $this->scoreGlobal; }
    public function setScoreGlobal(float $s): static { $this->scoreGlobal = $s; return $this; }

    public function getNbCveActives(): int { return $this->nbCveActives; }
    public function setNbCveActives(int $n): static { $this->nbCveActives = $n; return $this; }

    public function getDonnees(): ?array { return $this->donnees; }
    public function setDonnees(?array $d): static { $this->donnees = $d; return $this; }

    public function getDateSnapshot(): \DateTimeInterface { return $this->dateSnapshot; }
    public function setDateSnapshot(\DateTimeInterface $d): static { $this->dateSnapshot = $d; return $this; }
}
