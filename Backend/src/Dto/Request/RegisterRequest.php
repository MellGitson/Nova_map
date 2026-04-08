<?php

declare(strict_types=1);

namespace App\Dto\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class RegisterRequest
{
    #[Assert\NotBlank(message: 'L\'email est obligatoire.')]
    #[Assert\Email(message: 'Format email invalide.')]
    #[Assert\Length(max: 255)]
    public string $email = '';

    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire.')]
    #[Assert\Length(min: 12, minMessage: 'Le mot de passe doit contenir au moins 12 caractères.')]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
        message: 'Le mot de passe doit contenir minuscule, majuscule, chiffre et caractère spécial.'
    )]
    public string $password = '';

    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(max: 100)]
    public string $nom = '';

    /** Consentement RGPD explicite — obligatoire */
    #[Assert\IsTrue(message: 'Vous devez accepter les conditions d\'utilisation.')]
    public bool $consentementRgpd = false;
}
