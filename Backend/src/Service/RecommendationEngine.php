<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\TrajetRepository;

/**
 * Moteur de recommandation basé sur des règles métier simples.
 * Analyse les patterns de navigation et génère des suggestions.
 */
final class RecommendationEngine
{
    private const SEUIL_WEEKEND   = 3;
    private const SEUIL_FREQUENCE = 5;

    public function __construct(
        private readonly TrajetRepository $trajetRepo,
    ) {}

    /**
     * Génère des suggestions personnalisées pour un utilisateur.
     *
     * @return array{suggestions: list<array{type: string, titre: string, description: string, checklist?: list<string>}>}
     */
    public function generer(User $user): array
    {
        $suggestions = [];

        $trajetsRecents    = $this->trajetRepo->findRecentsByCapitaine($user->getId(), 30);
        $nbTrajetsWeekend  = $this->trajetRepo->countTrajetsWeekend($user->getId());
        $nbTrajetsTotal    = count($trajetsRecents);

        // Règle 1 : pattern "navigation weekend" → checklist pré-départ
        if ($nbTrajetsWeekend >= self::SEUIL_WEEKEND) {
            $suggestions[] = [
                'type'        => 'CHECKLIST',
                'titre'       => 'Checklist pré-départ weekend',
                'description' => 'Vous naviguez régulièrement le weekend. Voici votre checklist habituelle.',
                'checklist'   => [
                    'Vérifier le niveau de carburant',
                    'Contrôler les gilets de sauvetage',
                    'Tester les feux de navigation',
                    'Vérifier la météo marine (Météo-France)',
                    'Informer un proche de votre itinéraire',
                    'Contrôler le signal VHF',
                ],
            ];
        }

        // Règle 2 : utilisateur actif → suggestion d'entretien périodique
        if ($nbTrajetsTotal >= self::SEUIL_FREQUENCE) {
            $suggestions[] = [
                'type'        => 'ENTRETIEN',
                'titre'       => 'Entretien recommandé',
                'description' => 'Vous avez effectué ' . $nbTrajetsTotal . ' trajets ce mois-ci. Pensez à la vérification périodique du moteur.',
                'checklist'   => [
                    'Vidange moteur',
                    'Contrôle de l\'anode',
                    'Graissage des manœuvres',
                    'Inspection de la coque',
                ],
            ];
        }

        // Règle 3 : trajets récents sans distance renseignée
        $sansDistance = array_filter($trajetsRecents, fn ($t) => $t->getDistanceMilles() === null);
        if (count($sansDistance) > 2) {
            $suggestions[] = [
                'type'        => 'DONNEES',
                'titre'       => 'Complétez vos trajets',
                'description' => 'Plusieurs de vos trajets récents n\'ont pas de distance renseignée. Complétez-les pour des statistiques précises.',
            ];
        }

        return ['suggestions' => $suggestions];
    }
}
