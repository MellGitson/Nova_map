<?php

declare(strict_types=1);

namespace App\Dto\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class TrajetRequest
{
    #[Assert\NotBlank]
    public string $bateauId = '';

    #[Assert\Length(max: 150)]
    public ?string $portDepart = null;

    #[Assert\Length(max: 150)]
    public ?string $portArrivee = null;

    #[Assert\NotBlank]
    public string $dateDepart = '';

    public ?string $dateArrivee = null;

    #[Assert\PositiveOrZero]
    public ?float $distanceMilles = null;

    #[Assert\PositiveOrZero]
    public ?int $nombrePassagers = null;

    #[Assert\Length(max: 2000)]
    public ?string $notes = null;

    /** Conditions météo : ['vent' => '15kn', 'etat_mer' => '2', ...] */
    public ?array $conditionsMeteo = null;
}
