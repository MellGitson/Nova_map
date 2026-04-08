<?php

declare(strict_types=1);

namespace App\Service\Analyse;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Analyse la configuration SSL/TLS d'un domaine via l'API SSL Labs (Qualys).
 *
 * SSL Labs évalue la robustesse de la configuration HTTPS d'un serveur :
 * version du protocole (TLS 1.2 / 1.3), force du chiffrement, validité
 * du certificat, activation du HSTS, etc.
 * L'analyse est asynchrone — ce service effectue un polling jusqu'au résultat.
 *
 * Pénalité maximale : −25 points sur le score du composant.
 *   - Grade A+/A  :   0 pt  (configuration exemplaire)
 *   - Grade A-    :  −2 pts (légèrement en dessous)
 *   - Grade B     :  −5 pts (problèmes mineurs)
 *   - Grade C     : −10 pts (protocoles obsolètes TLS 1.0/1.1)
 *   - Grade D/E/F : −20 pts (configuration dangereuse)
 *   - Grade T     : −25 pts (certificat non fiable)
 *   - Grade M     : −15 pts (nom de domaine ne correspond pas)
 */
class AnalyseurSsl
{
    /** Pénalité maximale que ce service peut appliquer au score. */
    public const PENALITE_MAX = 25;

    /** Délai entre deux appels de vérification du statut SSL Labs (secondes). */
    private const DELAI_ATTENTE_SECONDES = 10;

    /** Nombre maximum de vérifications avant abandon. */
    private const MAX_VERIFICATIONS = 30;

    /** Pénalité associée à chaque grade SSL Labs. */
    private const PENALITE_PAR_GRADE = [
        'A+' =>  0,
        'A'  =>  0,
        'A-' =>  2,
        'B'  =>  5,
        'C'  => 10,
        'D'  => 15,
        'E'  => 20,
        'F'  => 20,
        'T'  => 25, // certificat non fiable
        'M'  => 15, // mismatch nom de domaine
    ];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {}

    /**
     * Lance l'analyse SSL Labs pour un domaine et attend le résultat final.
     *
     * @return array{
     *   grade: string,
     *   protocoles_actifs: string[],
     *   jours_avant_expiration: int,
     *   hsts_active: bool,
     *   donnees_brutes: array,
     *   penalite: int
     * }
     */
    public function analyser(string $domaine): array
    {
        // TODO Jalon 5 — cycle requête + polling SSL Labs
        // 1. POST https://api.ssllabs.com/api/v3/analyze?host={domaine}&startNew=on&all=done
        // 2. Polling GET /analyze?host={domaine} jusqu'à status == "READY"
        // 3. Extraire : endpoints[0].grade, protocoles, certExpiry, headers.HSTS
        throw new \LogicException('AnalyseurSsl::analyser() non implémenté (Jalon 5).');
    }

    /**
     * Calcule le nombre de jours restants avant l'expiration du certificat.
     *
     * @param int $expirationEnMillisecondes Timestamp SSL Labs (en ms)
     */
    public function joursAvantExpiration(int $expirationEnMillisecondes): int
    {
        $dateExpiration = new \DateTimeImmutable('@' . intdiv($expirationEnMillisecondes, 1000));
        $maintenant     = new \DateTimeImmutable();

        return max(0, (int) $maintenant->diff($dateExpiration)->days);
    }

    /**
     * Calcule la pénalité SSL (0 à 25) à partir du grade obtenu.
     */
    public function calculerPenalite(string $grade): int
    {
        return self::PENALITE_PAR_GRADE[$grade] ?? self::PENALITE_MAX;
    }
}
