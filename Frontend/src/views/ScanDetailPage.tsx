/**
 * Vue : graphe interactif D3 d'un projet (nœuds = composants, arêtes = liens).
 * Anciennement ScanDetailPage — remplacée par VueGraphePage.
 */
import { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { Button } from "../components";
import { GrapheInfra } from "../components/GrapheInfra";
import { obtenirGraphe, obtenirProjet } from "../api";
import type { Projet, DonneesGraphe, NoeudGraphe } from "../types";

export function ScanDetailPage() {
  const { projetId } = useParams<{ projetId: string }>();
  const navigate = useNavigate();

  const [projet, setProjet]   = useState<Projet | null>(null);
  const [graphe, setGraphe]   = useState<DonneesGraphe | null>(null);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur]   = useState<string | null>(null);

  useEffect(() => {
    if (!projetId) return;

    Promise.all([obtenirProjet(projetId), obtenirGraphe(projetId)])
      .then(([p, g]) => {
        setProjet(p);
        setGraphe(g);
      })
      .catch(() => setErreur("Impossible de charger le projet."))
      .finally(() => setChargement(false));
  }, [projetId]);

  const ouvrirDetailComposant = (noeud: NoeudGraphe) => {
    navigate(`/projets/${projetId}/composants/${noeud.id}`);
  };

  if (chargement) {
    return <div className="page"><p className="text-muted">Chargement du graphe…</p></div>;
  }

  if (erreur || !projet || !graphe) {
    return (
      <div className="page">
        <p className="auth-error">{erreur ?? "Projet introuvable."}</p>
        <Button onClick={() => navigate("/projets")}>Retour aux projets</Button>
      </div>
    );
  }

  return (
    <div className="page vue-graphe">
      {/* En-tête du projet */}
      <div className="dashboard-entete" style={{ marginBottom: "1rem" }}>
        <div>
          <h1>{projet.nom}</h1>
          {projet.description && (
            <p className="text-muted">{projet.description}</p>
          )}
        </div>
        <div style={{ display: "flex", gap: "0.75rem", alignItems: "center" }}>
          {projet.score_global !== null && (
            <span
              style={{
                padding: "0.4rem 1rem",
                borderRadius: "20px",
                fontWeight: 600,
                fontSize: "0.9rem",
                background: "#1a1a2e",
                border: "1px solid #333",
                color: scoreVersColor(projet.score_global),
              }}
            >
              Score global : {projet.score_global}/100
            </span>
          )}
          <Button
            variant="secondary"
            onClick={() => navigate("/projets/nouveau/composant", { state: { projetId } })}
          >
            + Composant
          </Button>
          <Button variant="secondary" onClick={() => navigate("/projets")}>
            ← Projets
          </Button>
        </div>
      </div>

      {/* Légende des types */}
      <div className="legende-graphe">
        {(["serveur", "bdd", "api", "cdn", "cloud"] as const).map((type) => (
          <span key={type} className="legende-item">
            <span
              className="legende-couleur"
              style={{ background: COULEUR_TYPE[type] }}
            />
            {type.toUpperCase()}
          </span>
        ))}
      </div>

      {/* Graphe D3 */}
      <div className="conteneur-graphe">
        <GrapheInfra
          noeuds={graphe.nodes}
          liens={graphe.edges}
          onClicNoeud={ouvrirDetailComposant}
        />
      </div>

      {/* Résumé des composants */}
      {graphe.nodes.length > 0 && (
        <div className="resume-composants">
          <h3>
            {graphe.nodes.length} composant{graphe.nodes.length > 1 ? "s" : ""}
          </h3>
          <ul className="liste-noeuds">
            {graphe.nodes.map((n) => (
              <li
                key={n.id}
                className="noeud-item"
                onClick={() => ouvrirDetailComposant(n)}
              >
                <span>{n.nom}</span>
                <span className="text-muted" style={{ fontSize: "0.85rem" }}>
                  {n.type}
                </span>
                {n.score !== null && (
                  <span style={{ color: scoreVersColor(n.score), fontWeight: 600 }}>
                    {n.score}/100
                  </span>
                )}
              </li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );
}

// Alias NovaMap
export { ScanDetailPage as VueGraphePage };

const COULEUR_TYPE: Record<string, string> = {
  serveur : "#4fc3f7",
  bdd     : "#a5d6a7",
  api     : "#ffb74d",
  cdn     : "#ce93d8",
  cloud   : "#ef9a9a",
};

function scoreVersColor(score: number): string {
  if (score >= 80) return "#00e676";
  if (score >= 50) return "#ffc107";
  if (score >= 20) return "#ff5722";
  return "#f44336";
}
