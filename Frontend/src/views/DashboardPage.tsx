import { useEffect, useState } from "react";
import { useNavigate, Link } from "react-router-dom";
import { Button } from "../components";
import { useAuth } from "../contexts";
import { listerProjets } from "../api";
import type { Projet } from "../types";

export function DashboardPage() {
  const { utilisateur } = useAuth();
  const navigate = useNavigate();
  const [projets, setProjets] = useState<Projet[]>([]);
  const [chargement, setChargement] = useState(true);

  useEffect(() => {
    listerProjets()
      .then(setProjets)
      .catch(() => setProjets([]))
      .finally(() => setChargement(false));
  }, []);

  // Calcul des métriques globales
  const projetsAvecScore = projets.filter((p) => p.score_global !== null);
  const scoreMoyen =
    projetsAvecScore.length > 0
      ? Math.round(
          projetsAvecScore.reduce((acc, p) => acc + (p.score_global ?? 0), 0) /
            projetsAvecScore.length
        )
      : null;

  const nbComposantsTotal = projets.reduce((acc, p) => acc + p.nb_composants, 0);

  if (chargement) {
    return (
      <div className="page dashboard-page">
        <p className="text-muted">Chargement…</p>
      </div>
    );
  }

  return (
    <div className="page dashboard-page">
      <div className="dashboard-entete">
        <div>
          <h1>Tableau de bord</h1>
          <p className="text-muted">
            Bienvenue, {utilisateur?.nom || utilisateur?.email}.
          </p>
        </div>
        <Button onClick={() => navigate("/projets/nouveau")}>
          + Nouveau projet
        </Button>
      </div>

      {/* Métriques globales */}
      <div className="metriques-globales">
        <div className="metrique-carte">
          <span className="metrique-valeur">{projets.length}</span>
          <span className="metrique-label">Projets</span>
        </div>
        <div className="metrique-carte">
          <span className="metrique-valeur">{nbComposantsTotal}</span>
          <span className="metrique-label">Composants</span>
        </div>
        <div className="metrique-carte">
          <span
            className="metrique-valeur"
            style={{ color: scoreMoyen !== null ? scoreVersColor(scoreMoyen) : "inherit" }}
          >
            {scoreMoyen !== null ? scoreMoyen : "—"}
          </span>
          <span className="metrique-label">Score moyen</span>
        </div>
      </div>

      {/* Liste des projets récents */}
      {projets.length > 0 ? (
        <section>
          <div className="section-entete">
            <h2>Projets récents</h2>
            <Link to="/projets" className="voir-tout">Voir tous →</Link>
          </div>
          <ul className="liste-projets">
            {projets.slice(0, 5).map((p) => (
              <li key={p.id} className="projet-item">
                <div className="projet-info">
                  <strong>{p.nom}</strong>
                  <span className="text-muted">
                    {p.nb_composants} composant{p.nb_composants !== 1 ? "s" : ""}
                  </span>
                </div>
                <div className="projet-actions">
                  {p.score_global !== null && (
                    <span
                      className="badge-score"
                      style={{ color: scoreVersColor(p.score_global) }}
                    >
                      Score {p.score_global}/100
                    </span>
                  )}
                  <Link to={`/projets/${p.id}`} className="btn-lien">
                    Voir le graphe →
                  </Link>
                </div>
              </li>
            ))}
          </ul>
        </section>
      ) : (
        <section className="dashboard-vide">
          <p>Aucun projet créé pour le moment.</p>
          <Button onClick={() => navigate("/projets/nouveau")}>
            Créer mon premier projet
          </Button>
        </section>
      )}
    </div>
  );
}

function scoreVersColor(score: number): string {
  if (score >= 80) return "#00e676";   // vert
  if (score >= 50) return "#ffc107";   // orange
  if (score >= 20) return "#ff5722";   // rouge
  return "#f44336";                    // rouge critique
}
