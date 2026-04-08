<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Projet;
use App\Entity\User;
use App\Repository\MembreRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Contrôle d'accès sur les Projets.
 *
 * VIEW   : créateur OU membre de l'organisation OU admin
 * EDIT   : créateur OU manager/admin de l'organisation
 * DELETE : créateur OU admin
 * EXPORT : créateur OU manager/admin
 */
class ProjetVoter extends Voter
{
    public const VIEW   = 'PROJECT_VIEW';
    public const EDIT   = 'PROJECT_EDIT';
    public const DELETE = 'PROJECT_DELETE';
    public const EXPORT = 'PROJECT_EXPORT';

    public function __construct(private readonly MembreRepository $membreRepository) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::EXPORT], true)
            && $subject instanceof Projet;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Projet $projet */
        $projet = $subject;

        // Admin global : accès total
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        $membre = $this->membreRepository->findOneBy([
            'utilisateur'  => $user,
            'organisation' => $projet->getOrganisation(),
        ]);

        return match ($attribute) {
            self::VIEW   => $projet->getCreateur() === $user || $membre !== null,
            self::EDIT   => $projet->getCreateur() === $user
                            || ($membre !== null && in_array($membre->getRole(), ['manager', 'admin'], true)),
            self::DELETE => $projet->getCreateur() === $user,
            self::EXPORT => $projet->getCreateur() === $user
                            || ($membre !== null && in_array($membre->getRole(), ['manager', 'admin'], true)),
            default      => false,
        };
    }
}
