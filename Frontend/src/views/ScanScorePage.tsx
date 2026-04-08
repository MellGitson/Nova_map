/**
 * Vue : détail d'un composant (infos, score, CVE actives).
 * Anciennement ScanScorePage — remplacée par DetailComposantPage.
 */
import { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { Button } from "../components";
import { obtenirComposant } from "../api";
import type { Composant } from "../types";

export function ScanScorePage() {
  const { projetId, composantId } = useParams<{
    projetId: string;
    composantId: string;
  }>();
  const navigate = useNavigate();

  const [composant, setComposant] = useState<Composant | null>(null);
  const [chargement, setChargement]   = useState(true);
  const [erreur, setErreur]           = useState<string | null>(null);

  useEffect(() => {
    if (!projetId || !composantId) return;

    obtenirComposant(projetId, composantId)
      .then(setComposant)
      .catch(() => setErreur("Impossible de charger le composant."))
      .finally(() => setChargement(false));
  }, [projetId, composantId]);

  if (chargement) {
    return <div className="page"><p className="text-muted">Chargement…</p></div>;
  }

  if (erreur || !composant) {
    return (
      <div className="page">
        <p className="auth-error">{erreur ?? "Composant introuvable."}</p>
        <Button onClick={() => navigate(`/projets/${projetId}`)}>
          Retour au graphe
        </Button>
      </div>
    );
  }

  const cves = composant.cves_actives ?? [];
  const nbCritiques = cves.filter((c) => c.severite === "CRITICAL").length;
  const nbElevees   = cves.filter((c) => c.severite === "HIGH").length;

  return (
    <div className="page">
      <div className="dashboard-entete">
        <div>
          <h1>{composant.nom}</h1>
          <p className="text-muted">
            {composant.type.toUpperCase()} — {composant.environnement}
          </p>
        </div>
        <Button variant="secondary" onClick={() => navigate(`/projets/${projetId}`)}>
          ← Graphe
        </Button>
      </div>

      {/* Score du composant */}
      {composant.score !== null && (
        <div className="bloc-score">
          <span
            className="score-valeur"
            style={{ color: scoreVersColor(composant.score) }}
          >
            {composant.score}
          </span>
          <span className="score-label">/ 100</span>
          <span
            className="badge-risque"
            style={{ background: scoreVersBadge(composant.score) }}
          >
            {scoreVersNiveau(composant.score)}
          </span>
        </div>
      )}

      {/* Informations techniques */}
      <section className="section-infos">
        <h2>Informations</h2>
        <dl className="liste-infos">
          {composant.ip_ou_domaine && (
            <>
              <dt>IP / Domaine</dt>
              <dd><code>{composant.ip_ou_domaine}</code></dd>
            </>
          )}
          {composant.version_logicielle && (
            <>
              <dt>Version</dt>
              <dd>{composant.version_logicielle}</dd>
            </>
          )}
          {composant.port && (
            <>
              <dt>Port</dt>
              <dd>{composant.port}</dd>
            </>
          )}
          <dt>Dernière analyse</dt>
          <dd>
            {composant.derniere_analyse
              ? new Date(composant.derniere_analyse).toLocaleString("fr-FR")
              : "Jamais analysé"}
          </dd>
        </dl>
      </section>

      {/* CVE actives */}
      <section className="section-cves">
        <h2>
          Vulnérabilités actives ({cves.length})
          {nbCritiques > 0 && (
            <span className="badge-critique">{nbCritiques} critique{nbCritiques > 1 ? "s" : ""}</span>
          )}
          {nbElevees > 0 && (
            <span className="badge-eleve">{nbElevees} élevée{nbElevees > 1 ? "s" : ""}</span>
          )}
        </h2>

        {cves.length === 0 ? (
          <p className="text-muted">Aucune vulnérabilité active détectée.</p>
        ) : (
          <ul className="liste-cves">
            {cves.map((cve) => (
              <li key={cve.cve_id} className="cve-item">
                <div className="cve-entete">
                  <code className="cve-id">{cve.cve_id}</code>
                  <span
                    className="cve-badge"
                    style={{ color: cveVersColor(cve.severite) }}
                  >
                    {cve.severite}
                  </span>
                </div>
                <div className="cve-cvss">
                  Score CVSS :{" "}
                  <strong style={{ color: cveVersColor(cve.severite) }}>
                    {cve.cvss.toFixed(1)}
                  </strong>
                </div>
              </li>
            ))}
          </ul>
        )}
      </section>
    </div>
  );
}

// Alias NovaMap
export { ScanScorePage as DetailComposantPage };

function scoreVersColor(score: number): string {
  if (score >= 80) return "#00e676";
  if (score >= 50) return "#ffc107";
  if (score >= 20) return "#ff5722";
  return "#f44336";
}

function scoreVersBadge(score: number): string {
  if (score >= 80) return "rgba(0,230,118,0.15)";
  if (score >= 50) return "rgba(255,193,7,0.15)";
  if (score >= 20) return "rgba(255,87,34,0.15)";
  return "rgba(244,67,54,0.15)";
}

function scoreVersNiveau(score: number): string {
  if (score >= 80) return "Risque faible";
  if (score >= 50) return "Risque modéré";
  if (score >= 20) return "Risque élevé";
  return "Risque critique";
}

function cveVersColor(severite: string): string {
  switch (severite) {
    case "CRITICAL": return "#f44336";
    case "HIGH":     return "#ff5722";
    case "MEDIUM":   return "#ffc107";
    default:         return "#66bb6a";
  }
}
