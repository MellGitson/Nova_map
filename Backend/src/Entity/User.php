<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'utilisateur')]
#[ORM\UniqueConstraint(name: 'UNIQ_EMAIL', columns: ['email'])]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_USER  = 'ROLE_USER';
    public const ROLE_OWNER = 'ROLE_OWNER';
    public const ROLE_RENTER = 'ROLE_RENTER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    private string $email;

    #[ORM\Column(type: 'string', length: 255)]
    private string $password;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private string $nom;

    /** @var list<string> */
    #[ORM\Column(type: 'json')]
    private array $roles = [self::ROLE_USER];

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $telephone = null;

    /**
     * Numéro de permis bateau — chiffré au repos (Doctrine Encryption post-MVP).
     * Stocké en VARCHAR pour permettre le chiffrement symétrique.
     */
    #[ORM\Column(type: 'string', length: 512, nullable: true)]
    private ?string $numeroPermis = null;

    #[ORM\Column(type: 'boolean')]
    private bool $emailVerifie = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $tokenConfirmation = null;

    #[ORM\Column(type: 'integer')]
    private int $tentativesLogin = 0;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeInterface $bloqueJusqua = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeInterface $consentementRgpd = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeInterface $dateSuppression = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeInterface $dateCreation;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeInterface $dateModification;

    #[ORM\OneToMany(mappedBy: 'proprietaire', targetEntity: Bateau::class)]
    private Collection $bateaux;

    #[ORM\OneToMany(mappedBy: 'locataire', targetEntity: Reservation::class, cascade: ['remove'])]
    private Collection $reservations;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: RefreshToken::class, cascade: ['remove'])]
    private Collection $refreshTokens;

    public function __construct()
    {
        $this->refreshTokens    = new ArrayCollection();
        $this->bateaux          = new ArrayCollection();
        $this->reservations     = new ArrayCollection();
        $this->dateCreation     = new \DateTimeImmutable();
        $this->dateModification = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->dateModification = new \DateTimeImmutable();
    }

    public function getId(): string { return $this->id; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): static { $this->email = strtolower($email); return $this; }

    public function getUserIdentifier(): string { return $this->email; }

    public function getPassword(): string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = self::ROLE_USER;
        return array_unique($roles);
    }
    public function setRoles(array $roles): static { $this->roles = $roles; return $this; }

    public function eraseCredentials(): void {}

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(?string $t): static { $this->telephone = $t; return $this; }

    public function getNumeroPermis(): ?string { return $this->numeroPermis; }
    public function setNumeroPermis(?string $n): static { $this->numeroPermis = $n; return $this; }

    public function isEmailVerifie(): bool { return $this->emailVerifie; }
    public function setEmailVerifie(bool $v): static { $this->emailVerifie = $v; return $this; }

    public function getTokenConfirmation(): ?string { return $this->tokenConfirmation; }
    public function setTokenConfirmation(?string $t): static { $this->tokenConfirmation = $t; return $this; }

    public function getTentativesLogin(): int { return $this->tentativesLogin; }
    public function setTentativesLogin(int $n): static { $this->tentativesLogin = $n; return $this; }
    public function incrementTentativesLogin(): static { $this->tentativesLogin++; return $this; }
    public function resetTentativesLogin(): static { $this->tentativesLogin = 0; return $this; }

    public function getBloqueJusqua(): ?\DateTimeInterface { return $this->bloqueJusqua; }
    public function setBloqueJusqua(?\DateTimeInterface $d): static { $this->bloqueJusqua = $d; return $this; }
    public function isBloque(): bool
    {
        return $this->bloqueJusqua !== null && $this->bloqueJusqua > new \DateTimeImmutable();
    }

    public function getConsentementRgpd(): ?\DateTimeInterface { return $this->consentementRgpd; }
    public function setConsentementRgpd(?\DateTimeInterface $d): static { $this->consentementRgpd = $d; return $this; }

    public function getDateSuppression(): ?\DateTimeInterface { return $this->dateSuppression; }
    public function setDateSuppression(?\DateTimeInterface $d): static { $this->dateSuppression = $d; return $this; }
    public function isSuprime(): bool { return $this->dateSuppression !== null; }

    public function getDateCreation(): \DateTimeInterface { return $this->dateCreation; }
    public function getDateModification(): \DateTimeInterface { return $this->dateModification; }

    public function getRefreshTokens(): Collection { return $this->refreshTokens; }
    public function getBateaux(): Collection { return $this->bateaux; }
    public function getReservations(): Collection { return $this->reservations; }
}
