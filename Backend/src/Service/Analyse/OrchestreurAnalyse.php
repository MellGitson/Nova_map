<?php

declare(strict_types=1);

namespace App\Service\Analyse;

use App\Entity\Composant;
use App\Entity\ResultatApi;
use App\Service\CalculateurScore;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Lance et coordonne toutes les analyses de sécurité pour un composant.
 *
 * Cet orchestrateur déclenche en parallèle (via HttpClient async) les cinq
 * analyseurs spécialisés, collecte leurs résultats, persiste les ResultatApi
 * en base, puis demande au CalculateurScore de calculer le score final.
 *
 * Ordre d'exécution :
 *   1. AnalyseurPorts  (Shodan)          — ports ouverts exposés
 *   2. AnalyseurCve    (NVD)             — vulnérabilités logiciel
 *   3. AnalyseurSsl    (SSL Labs)        — certificat et configuration TLS
 *   4. AnalyseurEntetes (SecurityHeaders) — en-têtes HTTP de sécurité
 *   5. AnalyseurIp     (IPinfo)          — réputation de l'adresse IP
 *
 * À la fin, le score du composant est mis à jour et une entrée Analyse
 * est créée en base avec le statut "terminee".
 */
class OrchestreurAnalyse
{
    public function __construct(
        private readonly AnalyseurPorts   $analyseurPorts,
        private readonly AnalyseurCve     $analyseurCve,
        private readonly AnalyseurSsl     $analyseurSsl,
        private readonly AnalyseurEntetes $analyseurEntetes,
        private readonly AnalyseurIp      $analyseurIp,
        private readonly CalculateurScore $calculateurScore,
        private readonly EntityManagerInterface $em,
    ) {}

    /**
     * Déclenche l'analyse complète d'un composant.
     *
     * @return array{
     *   score_avant: int,
     *   score_apres: int,
     *   penalites: array<string, int>,
     *   resultats: array<string, array>
     * }
     */
    public function analyserComposant(Composant $composant): array
    {
        // TODO Jalon 5 — implémenter le déclenchement parallèle des analyseurs
        //
        // Étapes :
        // 1. Récupérer ip_ou_domaine et version_logicielle du composant
        // 2. Lancer les 5 analyseurs (en async avec HttpClient si possible)
        // 3. Collecter les résultats et créer un ResultatApi par analyseur
        // 4. Appeler CalculateurScore::calculer() avec les 5 pénalités
        // 5. Mettre à jour composant->setScore() et composant->setDerniereAnalyse()
        // 6. Persister en base et retourner le résumé
        throw new \LogicException('OrchestreurAnalyse::analyserComposant() non implémenté (Jalon 5).');
    }

    /**
     * Lance l'analyse pour tous les composants d'un projet.
     * Utile pour l'analyse planifiée (cron) ou déclenchée manuellement.
     *
     * @param Composant[] $composants
     * @return array<string, array> Résultats indexés par ID de composant
     */
    public function analyserListe(array $composants): array
    {
        // TODO Jalon 5 — boucler sur analyserComposant() pour chaque composant
        throw new \LogicException('OrchestreurAnalyse::analyserListe() non implémenté (Jalon 5).');
    }
}
