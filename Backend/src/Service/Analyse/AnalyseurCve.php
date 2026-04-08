<?php

declare(strict_types=1);

namespace App\Service\Analyse;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Recherche les vulnérabilités CVE d'un logiciel via la base NVD/NIST.
 *
 * Le NVD (National Vulnerability Database) est le registre officiel américain
 * des CVE. Ce service recherche les vulnérabilités connues pour un logiciel
 * et sa version, puis calcule la pénalité selon leur niveau de gravité.
 *
 * Pénalité maximale : −30 points sur le score du composant.
 *   - CVE Critique (CVSS ≥ 9.0) : −10 pts par CVE, max −20
 *   - CVE Élevée  (CVSS 7.0–8.9): −5 pts  par CVE, max −15
 *   - CVE Moyenne (CVSS 4.0–6.9): −2 pts  par CVE, max −10
 *   - CVE Faible  (CVSS < 4.0)  : −1 pt   par CVE, max −5
 */
class AnalyseurCve
{
    /** Pénalité maximale que ce service peut appliquer au score. */
    public const PENALITE_MAX = 30;

    /** Seuils CVSS définissant chaque niveau de gravité. */
    private const SEUILS_GRAVITE = [
        'critique' => 9.0,
        'elevee'   => 7.0,
        'moyenne'  => 4.0,
    ];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $cleApiNvd,
    ) {}

    /**
     * Recherche les CVE pour un logiciel et une version donnés.
     *
     * @param string $nomLogiciel  Ex : "nginx", "openssh", "mysql"
     * @param string $version      Ex : "1.18.0", "8.0.32"
     *
     * @return array{
     *   cves: array<array{identifiant: string, cvss: float, gravite: string, correctif: bool}>,
     *   donnees_brutes: array,
     *   penalite: int
     * }
     */
    public function analyser(string $nomLogiciel, string $version): array
    {
        // TODO Jalon 5 — GET https://services.nvd.nist.gov/rest/json/cves/2.0
        //   ?keywordSearch={nomLogiciel}&versionEnd={version}
        // Gérer la pagination (resultsPerPage / startIndex)
        // Pour chaque CVE : extraire cveId, cvssMetricV31.baseScore, descriptions[lang=en]
        throw new \LogicException('AnalyseurCve::analyser() non implémenté (Jalon 5).');
    }

    /**
     * Détermine le niveau de gravité d'une CVE à partir de son score CVSS.
     */
    public function niveauGravite(float $cvss): string
    {
        return match (true) {
            $cvss >= self::SEUILS_GRAVITE['critique'] => 'critique',
            $cvss >= self::SEUILS_GRAVITE['elevee']   => 'elevee',
            $cvss >= self::SEUILS_GRAVITE['moyenne']  => 'moyenne',
            default                                   => 'faible',
        };
    }

    /**
     * Calcule la pénalité CVE (0 à 30) à partir d'une liste de CVE.
     *
     * @param array<array{cvss: float}> $cves
     */
    public function calculerPenalite(array $cves): int
    {
        $penalite = 0;
        foreach ($cves as $cve) {
            $penalite += match ($this->niveauGravite($cve['cvss'])) {
                'critique' => 10,
                'elevee'   => 5,
                'moyenne'  => 2,
                default    => 1,
            };
        }

        return min($penalite, self::PENALITE_MAX);
    }
}
