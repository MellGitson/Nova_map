<?php

declare(strict_types=1);

namespace App\Dto\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class PortRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    public string $nom = '';

    #[Assert\Length(max: 255)]
    public ?string $ville = null;

    #[Assert\Length(max: 100)]
    public ?string $pays = null;

    #[Assert\NotBlank]
    #[Assert\Range(min: -90, max: 90)]
    public float $latitude = 0.0;

    #[Assert\NotBlank]
    #[Assert\Range(min: -180, max: 180)]
    public float $longitude = 0.0;

    #[Assert\PositiveOrZero]
    public ?int $capacite = null;

    #[Assert\Length(max: 2000)]
    public ?string $description = null;
}
