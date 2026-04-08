<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Composant;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ComposantVoter extends Voter
{
    public const VIEW   = 'COMPOSANT_VIEW';
    public const EDIT   = 'COMPOSANT_EDIT';
    public const DELETE = 'COMPOSANT_DELETE';

    public function __construct(
        private readonly AuthorizationCheckerInterface $authChecker,
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE], true)
            && $subject instanceof Composant;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Composant $composant */
        $composant = $subject;
        $projet    = $composant->getProjet();

        // Délègue au ProjetVoter — même règles que le projet parent
        return match ($attribute) {
            self::VIEW   => $this->authChecker->isGranted(ProjetVoter::VIEW, $projet),
            self::EDIT   => $this->authChecker->isGranted(ProjetVoter::EDIT, $projet),
            self::DELETE => $this->authChecker->isGranted(ProjetVoter::EDIT, $projet),
            default      => false,
        };
    }
}
