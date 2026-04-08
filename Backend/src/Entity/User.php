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
#[ORM\UniqueConstraint(name: 'UNIQ_OAUTH_GOOGLE', columns: ['oauth_google_id'])]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
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
    private array $roles = ['ROLE_USER'];

    #[ORM\Column(type: 'string', length: 255, nullable: true, unique: true)]
    private ?string $oauthGoogleId = null;

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

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeInterface $dateCreation;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeInterface $dateModification;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: RefreshToken::class, cascade: ['remove'])]
    private Collection $refreshTokens;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Membre::class, cascade: ['remove'])]
    private Collection $memberships;

    #[ORM\OneToMany(mappedBy: 'createur', targetEntity: Projet::class)]
    private Collection $projets;

    public function __construct()
    {
        $this->refreshTokens = new ArrayCollection();
        $this->memberships   = new ArrayCollection();
        $this->projets       = new ArrayCollection();
        $this->dateCreation  = new \DateTimeImmutable();
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
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }
    public function setRoles(array $roles): static { $this->roles = $roles; return $this; }

    public function eraseCredentials(): void {}

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getOauthGoogleId(): ?string { return $this->oauthGoogleId; }
    public function setOauthGoogleId(?string $id): static { $this->oauthGoogleId = $id; return $this; }

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

    public function getDateCreation(): \DateTimeInterface { return $this->dateCreation; }
    public function getDateModification(): \DateTimeInterface { return $this->dateModification; }

    public function getRefreshTokens(): Collection { return $this->refreshTokens; }
    public function getMemberships(): Collection { return $this->memberships; }
    public function getProjets(): Collection { return $this->projets; }
}
