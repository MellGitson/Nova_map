/**
 * Vue : formulaire de création d'un projet.
 * Anciennement CreateScanPage — remplacée par CreerProjetPage.
 */
import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { Button } from "../components";
import { creerProjet } from "../api";

export function CreateScanPage() {
  const navigate = useNavigate();
  const [nom, setNom] = useState("");
  const [description, setDescription] = useState("");
  const [erreur, setErreur] = useState<string | null>(null);
  const [chargement, setChargement] = useState(false);

  const handleSoumission = async (e: React.FormEvent) => {
    e.preventDefault();
    setErreur(null);

    if (!nom.trim()) {
      setErreur("Le nom du projet est obligatoire.");
      return;
    }

    setChargement(true);
    try {
      const projet = await creerProjet({
        nom: nom.trim(),
        description: description.trim() || null,
      });
      navigate(`/projets/${projet.id}`, { replace: true });
    } catch (err) {
      setErreur(err instanceof Error ? err.message : "Impossible de créer le projet.");
    } finally {
      setChargement(false);
    }
  };

  return (
    <div className="page">
      <div className="dashboard-entete">
        <h1>Nouveau projet</h1>
      </div>
      <p className="text-muted">
        Un projet regroupe tous les composants de votre infrastructure (serveurs,
        bases de données, APIs…) et leurs interconnexions.
      </p>

      {erreur && <p className="auth-error">{erreur}</p>}

      <form onSubmit={handleSoumission} className="auth-form" style={{ maxWidth: "560px" }}>
        <label>
          <span>Nom du projet *</span>
          <input
            type="text"
            value={nom}
            onChange={(e) => setNom(e.target.value)}
            placeholder="Ex : Infrastructure production, API Gateway…"
            required
            maxLength={100}
            disabled={chargement}
          />
        </label>
        <label>
          <span>Description (optionnelle)</span>
          <textarea
            value={description}
            onChange={(e) => setDescription(e.target.value)}
            placeholder="Décrivez brièvement l'infrastructure cartographiée…"
            rows={4}
            maxLength={500}
            disabled={chargement}
            style={{
              resize: "vertical",
              padding: "0.65rem 0.9rem",
              background: "var(--bg-elevated)",
              border: "1px solid var(--border)",
              borderRadius: "8px",
              color: "var(--text)",
              fontFamily: "inherit",
            }}
          />
        </label>
        <div style={{ display: "flex", gap: "1rem" }}>
          <Button type="submit" disabled={chargement}>
            {chargement ? "Création…" : "Créer le projet"}
          </Button>
          <Button
            type="button"
            variant="secondary"
            onClick={() => navigate("/projets")}
            disabled={chargement}
          >
            Annuler
          </Button>
        </div>
      </form>
    </div>
  );
}

// Alias NovaMap
export { CreateScanPage as CreerProjetPage };
