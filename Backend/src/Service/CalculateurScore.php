<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Analyse\AnalyseurCve;
use App\Service\Analyse\AnalyseurEntetes;
use App\Service\Analyse\AnalyseurIp;
use App\Service\Analyse\AnalyseurPorts;
use App\Service\Analyse\AnalyseurSsl;

/**
 * Calcule le score de sécurité d'un composant à partir des résultats d'analyse.
 *
 * Le score est calculé selon la formule :
 *   Score = 100 − Σ pénalités
 *
 * Chaque analyseur contribue une pénalité partielle, plafonnée à son maximum :
 *   - Ports ouverts (Shodan)          : max −20 pts  → AnalyseurPorts::PENALITE_MAX
 *   - Vulnérabilités CVE (NVD)        : max −30 pts  → AnalyseurCve::PENALITE_MAX
 *   - Certificat SSL (SSL Labs)       : max −25 pts  → AnalyseurSsl::PENALITE_MAX
 *   - En-têtes HTTP (SecurityHeaders) : max −20 pts  → AnalyseurEntetes::PENALITE_MAX
 *   - Réputation IP (IPinfo)          : max −15 pts  → AnalyseurIp::PENALITE_MAX
 *
 * Total de pénalités possible : 110 pts → score minimum = 0 (jamais négatif).
 */
class CalculateurScore
{
    /** Score de départ avant toute pénalité. */
    private const SCORE_DEPART = 100;

    /**
     * Calcule le score final d'un composant à partir des 5 pénalités partielles.
     *
     * @param int $penalitePorts    Résultat de AnalyseurPorts::calculerPenalite()
     * @param int $penaliteCve      Résultat de AnalyseurCve::calculerPenalite()
     * @param int $penaliteSsl      Résultat de AnalyseurSsl::calculerPenalite()
     * @param int $penaliteEntetes  Résultat de AnalyseurEntetes::calculerPenalite()
     * @param int $penaliteIp       Résultat de AnalyseurIp::calculerPenalite()
     *
     * @return array{
     *   score: int,
     *   penalites: array{ports: int, cve: int, ssl: int, entetes: int, ip: int},
     *   total_penalites: int
     * }
     */
    public function calculer(
        int $penalitePorts,
        int $penaliteCve,
        int $penaliteSsl,
        int $penaliteEntetes,
        int $penaliteIp,
    ): array {
        // Plafonner chaque pénalité à son maximum défini dans l'analyseur correspondant
        $penalitePorts   = min($penalitePorts,   AnalyseurPorts::PENALITE_MAX);
        $penaliteCve     = min($penaliteCve,     AnalyseurCve::PENALITE_MAX);
        $penaliteSsl     = min($penaliteSsl,     AnalyseurSsl::PENALITE_MAX);
        $penaliteEntetes = min($penaliteEntetes, AnalyseurEntetes::PENALITE_MAX);
        $penaliteIp      = min($penaliteIp,      AnalyseurIp::PENALITE_MAX);

        $totalPenalites = $penalitePorts + $penaliteCve + $penaliteSsl + $penaliteEntetes + $penaliteIp;
        $score          = max(0, self::SCORE_DEPART - $totalPenalites);

        return [
            'score'          => $score,
            'penalites'      => [
                'ports'   => $penalitePorts,
                'cve'     => $penaliteCve,
                'ssl'     => $penaliteSsl,
                'entetes' => $penaliteEntetes,
                'ip'      => $penaliteIp,
            ],
            'total_penalites' => $totalPenalites,
        ];
    }

    /**
     * Détermine le niveau de risque à partir d'un score.
     *
     *   80–100 : Faible   (vert)
     *   50–79  : Modéré   (orange)
     *   20–49  : Élevé    (rouge)
     *   0–19   : Critique (rouge foncé)
     */
    public function niveauRisque(int $score): string
    {
        return match (true) {
            $score >= 80 => 'faible',
            $score >= 50 => 'modere',
            $score >= 20 => 'eleve',
            default      => 'critique',
        };
    }
}
