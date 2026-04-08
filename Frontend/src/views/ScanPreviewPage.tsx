/**
 * Vue : liste de tous les projets de l'utilisateur.
 * Anciennement ScanPreviewPage — remplacée par ListeProjetsPage.
 */
import { useEffect, useState } from "react";
import { useNavigate, Link } from "react-router-dom";
import { Button } from "../components";
import { listerProjets } from "../api";
import type { Projet } from "../types";

export function ScanPreviewPage() {
  const navigate = useNavigate();
  const [projets, setProjets]     = useState<Projet[]>([]);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur]       = useState<string | null>(null);

  useEffect(() => {
    listerProjets()
      .then(setProjets)
      .catch(() => setErreur("Impossible de charger les projets."))
      .finally(() => setChargement(false));
  }, []);

  if (chargement) {
    return <div className="page"><p className="text-muted">Chargement…</p></div>;
  }

  return (
    <div className="page">
      <div className="dashboard-entete">
        <h1>Mes projets</h1>
        <Button onClick={() => navigate("/projets/nouveau")}>+ Nouveau projet</Button>
      </div>

      {erreur && <p className="auth-error">{erreur}</p>}

      {projets.length === 0 ? (
        <div className="dashboard-vide">
          <p>Aucun projet pour l'instant.</p>
          <Button onClick={() => navigate("/projets/nouveau")}>
            Créer mon premier projet
          </Button>
        </div>
      ) : (
        <ul className="liste-projets">
          {projets.map((p) => (
            <li key={p.id} className="projet-item">
              <div className="projet-info">
                <strong>{p.nom}</strong>
                {p.description && (
                  <span className="text-muted">{p.description}</span>
                )}
                <span className="text-muted" style={{ fontSize: "0.85rem" }}>
                  {p.nb_composants} composant{p.nb_composants !== 1 ? "s" : ""} —
                  modifié le{" "}
                  {new Date(p.date_modification).toLocaleDateString("fr-FR")}
                </span>
              </div>
              <div className="projet-actions">
                {p.score_global !== null && (
                  <span
                    className="badge-score"
                    style={{ color: scoreVersColor(p.score_global) }}
                  >
                    {p.score_global}/100
                  </span>
                )}
                <Link to={`/projets/${p.id}`} className="btn-lien">
                  Graphe →
                </Link>
              </div>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}

// Alias NovaMap
export { ScanPreviewPage as ListeProjetsPage };

function scoreVersColor(score: number): string {
  if (score >= 80) return "#00e676";
  if (score >= 50) return "#ffc107";
  if (score >= 20) return "#ff5722";
  return "#f44336";
}
