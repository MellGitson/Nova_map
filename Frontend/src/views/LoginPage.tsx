import { useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { Button } from "../components";
import { useAuth } from "../contexts";
import { connecter } from "../api";

export function LoginPage() {
  const navigate = useNavigate();
  const { setUtilisateur } = useAuth();
  const [email, setEmail] = useState("");
  const [motDePasse, setMotDePasse] = useState("");
  const [erreur, setErreur] = useState<string | null>(null);
  const [chargement, setChargement] = useState(false);

  const handleSoumission = async (e: React.FormEvent) => {
    e.preventDefault();
    setErreur(null);
    setChargement(true);
    try {
      const utilisateur = await connecter({ email, password: motDePasse });
      setUtilisateur(utilisateur);
      navigate("/dashboard", { replace: true });
    } catch (err) {
      setErreur(err instanceof Error ? err.message : "Connexion impossible.");
    } finally {
      setChargement(false);
    }
  };

  return (
    <div className="page auth-page">
      <div className="auth-card">
        <h1>Connexion</h1>
        <p className="text-muted">Accédez à votre espace NovaMap</p>
        {erreur && <p className="auth-error">{erreur}</p>}
        <form onSubmit={handleSoumission} className="auth-form">
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
              autoComplete="current-password"
              disabled={chargement}
            />
          </label>
          <Button type="submit" className="full-width" disabled={chargement}>
            {chargement ? "Connexion…" : "Se connecter"}
          </Button>
        </form>
        <p className="auth-footer">
          Pas encore de compte ? <Link to="/register">S'inscrire</Link>
        </p>
      </div>
    </div>
  );
}
