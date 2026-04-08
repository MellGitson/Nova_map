<?php

declare(strict_types=1);

namespace App\Service\Analyse;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Vérifie la réputation d'une adresse IP via l'API IPinfo.
 *
 * IPinfo fournit des informations de géolocalisation et de réputation :
 * pays d'hébergement, opérateur réseau (ASN), type d'infrastructure
 * (datacenter, VPN, Tor, proxy) et signalement pour activité malveillante.
 *
 * Ce service permet de détecter si un composant est hébergé dans
 * une infrastructure à risque ou si son IP est associée à des abus connus.
 *
 * Pénalité maximale : −15 points sur le score du composant.
 *   - IP signalée pour abus (spam, botnet…) : −15 pts
 *   - IP Tor ou proxy anonymisant           : −10 pts
 *   - IP hébergée dans un datacenter        :  −5 pts
 *   - Pays d'hébergement à risque élevé     :  −5 pts
 */
class AnalyseurIp
{
    /** Pénalité maximale que ce service peut appliquer au score. */
    public const PENALITE_MAX = 15;

    /**
     * Codes pays ISO 3166-1 considérés à risque pour l'hébergement.
     * À adapter selon la politique de sécurité de l'organisation.
     */
    private const PAYS_A_RISQUE = ['CN', 'RU', 'KP', 'IR', 'SY'];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $cleApiIpinfo,
    ) {}

    /**
     * Analyse la réputation d'une adresse IP.
     *
     * @return array{
     *   ip: string,
     *   pays: string,
     *   operateur: string,
     *   est_datacenter: bool,
     *   est_vpn_ou_tor: bool,
     *   est_signale_abusif: bool,
     *   donnees_brutes: array,
     *   penalite: int
     * }
     */
    public function analyser(string $adresseIp): array
    {
        // TODO Jalon 5 — GET https://ipinfo.io/{ip}/json?token={cleApiIpinfo}
        // Champs utiles : country, org (contient le numéro ASN),
        // privacy.vpn, privacy.tor, privacy.proxy, abuse.score
        throw new \LogicException('AnalyseurIp::analyser() non implémenté (Jalon 5).');
    }

    /**
     * Calcule la pénalité IP (0 à 15) selon les caractéristiques détectées.
     */
    public function calculerPenalite(
        bool   $estSignaleAbusif,
        bool   $estVpnOuTor,
        bool   $estDatacenter,
        string $codePays,
    ): int {
        $penalite = 0;

        if ($estSignaleAbusif) {
            $penalite += 15;
        } elseif ($estVpnOuTor) {
            $penalite += 10;
        }

        if ($estDatacenter) {
            $penalite += 5;
        }

        if (in_array(strtoupper($codePays), self::PAYS_A_RISQUE, true)) {
            $penalite += 5;
        }

        return min($penalite, self::PENALITE_MAX);
    }
}
