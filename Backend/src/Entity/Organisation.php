<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OrganisationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrganisationRepository::class)]
#[ORM\Table(name: 'organisation')]
#[ORM\UniqueConstraint(name: 'UNIQ_SLUG', columns: ['slug'])]
class Organisation
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\Column(type: 'string', length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    private string $nom;

    #[ORM\Column(type: 'string', length: 150, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    private string $slug;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $proprietaire;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $dateCreation;

    #[ORM\OneToMany(mappedBy: 'organisation', targetEntity: Membre::class, cascade: ['remove'])]
    private Collection $membres;

    #[ORM\OneToMany(mappedBy: 'organisation', targetEntity: Projet::class, cascade: ['remove'])]
    private Collection $projets;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
        $this->membres      = new ArrayCollection();
        $this->projets      = new ArrayCollection();
    }

    public function getId(): string { return $this->id; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }

    public function getProprietaire(): User { return $this->proprietaire; }
    public function setProprietaire(User $u): static { $this->proprietaire = $u; return $this; }

    public function getDateCreation(): \DateTimeInterface { return $this->dateCreation; }

    public function getMembres(): Collection { return $this->membres; }
    public function getProjets(): Collection { return $this->projets; }
}
