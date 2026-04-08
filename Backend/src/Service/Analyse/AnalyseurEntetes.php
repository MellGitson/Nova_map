<?php

declare(strict_types=1);

namespace App\Service\Analyse;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Vérifie la présence des en-têtes de sécurité HTTP d'un domaine.
 *
 * Les en-têtes HTTP de sécurité sont des directives que le serveur envoie
 * au navigateur pour lui indiquer les politiques à respecter (blocage XSS,
 * forçage HTTPS, interdiction d'intégration dans une iframe, etc.).
 * Leur absence constitue une faiblesse de configuration.
 *
 * Pénalité maximale : −20 points sur le score du composant.
 *   - 1 en-tête critique manquant : −5 pts (4 en-têtes critiques surveillés)
 *
 * En-têtes critiques surveillés :
 *   - Strict-Transport-Security : force la connexion HTTPS (HSTS)
 *   - Content-Security-Policy   : prévient les injections XSS
 *   - X-Frame-Options           : bloque le clickjacking
 *   - X-Content-Type-Options    : bloque le MIME sniffing
 */
class AnalyseurEntetes
{
    /** Pénalité maximale que ce service peut appliquer au score. */
    public const PENALITE_MAX = 20;

    /** Pénalité par en-tête critique absent. */
    private const PENALITE_PAR_ENTETE = 5;

    /**
     * En-têtes critiques dont l'absence est pénalisante.
     * Clé = nom HTTP exact, valeur = rôle de cet en-tête.
     */
    private const ENTETES_CRITIQUES = [
        'strict-transport-security' => 'Force la connexion HTTPS (HSTS)',
        'content-security-policy'   => 'Prévient les injections XSS (CSP)',
        'x-frame-options'           => "Bloque l'intégration en iframe (clickjacking)",
        'x-content-type-options'    => 'Bloque la détection automatique de type MIME',
    ];

    /**
     * En-têtes recommandés mais non pénalisants s'ils sont absents.
     */
    private const ENTETES_RECOMMANDES = [
        'referrer-policy',
        'permissions-policy',
        'cross-origin-embedder-policy',
    ];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {}

    /**
     * Analyse les en-têtes HTTP de sécurité d'un domaine.
     *
     * @return array{
     *   entetes_presents: string[],
     *   entetes_manquants: string[],
     *   entetes_recommandes_absents: string[],
     *   donnees_brutes: array,
     *   penalite: int
     * }
     */
    public function analyser(string $domaine): array
    {
        // TODO Jalon 5 — implémenter l'analyse
        // 1. Requête HEAD sur https://{domaine}
        // 2. Récupérer les en-têtes de la réponse (en minuscules)
        // 3. Comparer avec ENTETES_CRITIQUES et ENTETES_RECOMMANDES
        // 4. Retourner le détail + appeler calculerPenalite()
        throw new \LogicException('AnalyseurEntetes::analyser() non implémenté (Jalon 5).');
    }

    /**
     * Calcule la pénalité (0 à 20) selon le nombre d'en-têtes critiques absents.
     *
     * @param string[] $entetesCritiquesAbsents Noms des en-têtes manquants
     */
    public function calculerPenalite(array $entetesCritiquesAbsents): int
    {
        return min(
            count($entetesCritiquesAbsents) * self::PENALITE_PAR_ENTETE,
            self::PENALITE_MAX
        );
    }

    /** Retourne les noms des en-têtes critiques surveillés. */
    public function listeEntetesCritiques(): array
    {
        return array_keys(self::ENTETES_CRITIQUES);
    }
}
