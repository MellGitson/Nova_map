import { useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { Button } from "../components";
import { inscrire } from "../api";

export function RegisterPage() {
  const navigate = useNavigate();
  const [email, setEmail] = useState("");
  const [nom, setNom] = useState("");
  const [motDePasse, setMotDePasse] = useState("");
  const [consentement, setConsentement] = useState(false);
  const [erreur, setErreur] = useState<string | null>(null);
  const [chargement, setChargement] = useState(false);

  const handleSoumission = async (e: React.FormEvent) => {
    e.preventDefault();
    setErreur(null);

    if (!consentement) {
      setErreur("Vous devez accepter la politique de confidentialité pour créer un compte.");
      return;
    }

    setChargement(true);
    try {
      await inscrire({
        email,
        password: motDePasse,
        nom,
        consentement_rgpd: true,
      });
      navigate("/register/confirm", { replace: true });
    } catch (err) {
      setErreur(err instanceof Error ? err.message : "Inscription impossible.");
    } finally {
      setChargement(false);
    }
  };

  return (
    <div className="page auth-page">
      <div className="auth-card">
        <h1>Créer un compte</h1>
        <p className="text-muted">Rejoignez NovaMap gratuitement</p>
        {erreur && <p className="auth-error">{erreur}</p>}
        <form onSubmit={handleSoumission} className="auth-form">
          <label>
            <span>Nom complet</span>
            <input
              type="text"
              value={nom}
              onChange={(e) => setNom(e.target.value)}
              required
              autoComplete="name"
              disabled={chargement}
            />
          </label>
          <label>
            <span>Email</span>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              autoComplete="email"
              disabled={chargement}
            />
          </label>
          <label>
            <span>Mot de passe</span>
            <input
              type="password"
              value={motDePasse}
              onChange={(e) => setMotDePasse(e.target.value)}
              required
              minLength={12}
              autoComplete="new-password"
              disabled={chargement}
            />
            <small className="text-muted">
              12 caractères minimum, avec majuscule, chiffre et caractère spécial.
            </small>
          </label>
          <label className="label-checkbox">
            <input
              type="checkbox"
              checked={consentement}
              onChange={(e) => setConsentement(e.target.checked)}
              disabled={chargement}
            />
            <span>
              J'accepte la{" "}
              <a href="#" target="_blank" rel="noopener noreferrer">
                politique de confidentialité
              </a>{" "}
              et le traitement de mes données personnelles (RGPD).
            </span>
          </label>
          <Button type="submit" className="full-width" disabled={chargement}>
            {chargement ? "Création…" : "Créer mon compte"}
          </Button>
        </form>
        <p className="auth-footer">
          Déjà un compte ? <Link to="/login">Se connecter</Link>
        </p>
      </div>
    </div>
  );
}
