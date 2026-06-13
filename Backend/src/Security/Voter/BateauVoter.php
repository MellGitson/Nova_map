<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Bateau;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Bateau>
 */
final class BateauVoter extends Voter
{
    public const EDIT   = 'BATEAU_EDIT';
    public const DELETE = 'BATEAU_DELETE';
    public const VIEW   = 'BATEAU_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE, self::VIEW], true)
            && $subject instanceof Bateau;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Bateau $bateau */
        $bateau = $subject;

        if (in_array(User::ROLE_ADMIN, $user->getRoles(), true)) {
            return true;
        }

        return match ($attribute) {
            self::VIEW   => true,
            self::EDIT,
            self::DELETE => $bateau->getProprietaire()->getId() === $user->getId(),
            default      => false,
        };
    }
}
