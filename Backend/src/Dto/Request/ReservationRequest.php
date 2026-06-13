<?php

declare(strict_types=1);

namespace App\Dto\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class ReservationRequest
{
    #[Assert\NotBlank]
    public string $bateauId = '';

    #[Assert\NotBlank]
    #[Assert\Date]
    public string $dateDebut = '';

    #[Assert\NotBlank]
    #[Assert\Date]
    public string $dateFin = '';

    #[Assert\Length(max: 500)]
    public ?string $commentaire = null;
}
