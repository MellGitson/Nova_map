<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Trajet;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Trajet>
 */
final class TrajetVoter extends Voter
{
    public const VIEW   = 'TRAJET_VIEW';
    public const EDIT   = 'TRAJET_EDIT';
    public const DELETE = 'TRAJET_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE], true)
            && $subject instanceof Trajet;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Trajet $trajet */
        $trajet = $subject;

        if (in_array(User::ROLE_ADMIN, $user->getRoles(), true)) {
            return true;
        }

        $isCapitaine    = $trajet->getCapitaine()->getId() === $user->getId();
        $isProprietaire = $trajet->getBateau()->getProprietaire()->getId() === $user->getId();

        return match ($attribute) {
            self::VIEW   => $isCapitaine || $isProprietaire,
            self::EDIT,
            self::DELETE => $isCapitaine,
            default      => false,
        };
    }
}
