<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LienComposantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LienComposantRepository::class)]
#[ORM\Table(name: 'lien_composant')]
class LienComposant
{
    public const TYPE_HTTP  = 'http';
    public const TYPE_TCP   = 'tcp';
    public const TYPE_GRPC  = 'grpc';
    public const TYPE_AMQP  = 'amqp';
    public const TYPE_SSH   = 'ssh';
    public const TYPE_AUTRE = 'autre';

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Composant::class, inversedBy: 'liensSource')]
    #[ORM\JoinColumn(name: 'source_id', nullable: false, onDelete: 'CASCADE')]
    private Composant $source;

    #[ORM\ManyToOne(targetEntity: Composant::class, inversedBy: 'liensCible')]
    #[ORM\JoinColumn(name: 'cible_id', nullable: false, onDelete: 'CASCADE')]
    private Composant $cible;

    #[ORM\Column(type: 'string', length: 20, columnDefinition: "ENUM('http','tcp','grpc','amqp','ssh','autre') NOT NULL DEFAULT 'http'")]
    private string $typeLien = self::TYPE_HTTP;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $description = null;

    public function getId(): string { return $this->id; }

    public function getSource(): Composant { return $this->source; }
    public function setSource(Composant $c): static { $this->source = $c; return $this; }

    public function getCible(): Composant { return $this->cible; }
    public function setCible(Composant $c): static { $this->cible = $c; return $this; }

    public function getTypeLien(): string { return $this->typeLien; }
    public function setTypeLien(string $t): static { $this->typeLien = $t; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $d): static { $this->description = $d; return $this; }
}
