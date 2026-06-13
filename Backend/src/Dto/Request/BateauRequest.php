<?php

declare(strict_types=1);

namespace App\Dto\Request;

use App\Entity\Bateau;
use Symfony\Component\Validator\Constraints as Assert;

final class BateauRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    public string $nom = '';

    #[Assert\Length(max: 100)]
    public ?string $type = null;

    #[Assert\Length(max: 100)]
    public ?string $marque = null;

    #[Assert\Positive]
    #[Assert\Range(min: 1900, max: 2100)]
    public ?int $annee = null;

    #[Assert\Positive]
    public ?float $longueur = null;

    #[Assert\Positive]
    public ?int $capacitePersonnes = null;

    #[Assert\PositiveOrZero]
    public ?float $prixParJour = null;

    #[Assert\Choice(choices: Bateau::STATUTS)]
    public string $statut = Bateau::STATUT_DISPONIBLE;

    #[Assert\Length(max: 2000)]
    public ?string $description = null;

    public ?string $portId = null;
}
