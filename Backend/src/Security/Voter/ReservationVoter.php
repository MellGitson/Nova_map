<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Reservation;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Reservation>
 */
final class ReservationVoter extends Voter
{
    public const VIEW   = 'RESERVATION_VIEW';
    public const CANCEL = 'RESERVATION_CANCEL';
    public const MANAGE = 'RESERVATION_MANAGE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::CANCEL, self::MANAGE], true)
            && $subject instanceof Reservation;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Reservation $reservation */
        $reservation = $subject;

        if (in_array(User::ROLE_ADMIN, $user->getRoles(), true)) {
            return true;
        }

        $isLocataire    = $reservation->getLocataire()->getId() === $user->getId();
        $isProprietaire = $reservation->getBateau()->getProprietaire()->getId() === $user->getId();

        return match ($attribute) {
            self::VIEW   => $isLocataire || $isProprietaire,
            self::CANCEL => $isLocataire,
            self::MANAGE => $isProprietaire,
            default      => false,
        };
    }
}
