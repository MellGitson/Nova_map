<?php

declare(strict_types=1);

namespace App\Service\Analyse;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Analyse les ports ouverts d'un composant via l'API Shodan.
 *
 * Shodan recense en continu les équipements exposés sur Internet.
 * Ce service interroge Shodan pour une IP donnée afin de détecter
 * les ports ouverts, les services exposés et les vulnérabilités
 * déjà indexées par Shodan.
 *
 * Pénalité maximale : −20 points sur le score du composant.
 *   - Port critique exposé (SSH, MySQL, Redis…) : −5 pts par port, max −15
 *   - CVE déjà indexée par Shodan               : −5 pts
 */
class AnalyseurPorts
{
    /** Pénalité maximale que ce service peut appliquer au score. */
    public const PENALITE_MAX = 20;

    /** Ports jugés critiques s'ils sont accessibles depuis Internet. */
    private const PORTS_CRITIQUES = [22, 23, 3306, 5432, 27017, 6379, 9200, 11211];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $cleApiShodan,
    ) {}

    /**
     * Lance l'analyse des ports pour une adresse IP.
     *
     * @return array{
     *   ports_ouverts: int[],
     *   ports_critiques: int[],
     *   cves_shodan: string[],
     *   donnees_brutes: array,
     *   penalite: int
     * }
     */
    public function analyser(string $adresseIp): array
    {
        // TODO Jalon 5 — GET https://api.shodan.io/shodan/host/{ip}?key={cleApiShodan}
        // Extraire : ports[], vulns[] (liste de CVE IDs), data[].transport
        throw new \LogicException('AnalyseurPorts::analyser() non implémenté (Jalon 5).');
    }

    /**
     * Calcule la pénalité (0 à 20) selon les ports critiques détectés
     * et les CVE déjà indexées par Shodan.
     *
     * @param int[]    $portsCritiquesDetectes
     * @param string[] $cvesShodan
     */
    public function calculerPenalite(array $portsCritiquesDetectes, array $cvesShodan): int
    {
        $penalite  = count($portsCritiquesDetectes) * 5;
        $penalite += count($cvesShodan) > 0 ? 5 : 0;

        return min($penalite, self::PENALITE_MAX);
    }

    /** Retourne la liste des ports jugés critiques. */
    public function listePortsCritiques(): array
    {
        return self::PORTS_CRITIQUES;
    }
}
