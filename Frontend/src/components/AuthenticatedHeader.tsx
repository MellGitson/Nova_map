import { Link, useNavigate } from "react-router-dom";
import { useAuth } from "../contexts";

export function AuthenticatedHeader() {
  const { deconnexion, utilisateur } = useAuth();
  const navigate = useNavigate();

  const handleDeconnexion = async () => {
    await deconnexion();
    navigate("/", { replace: true });
  };

  return (
    <header className="header">
      <div className="header-inner">
        <Link to="/dashboard" className="logo">
          NovaMap
        </Link>
        <nav className="nav">
          <Link to="/projets">Mes projets</Link>
          <span className="text-muted" style={{ fontSize: "0.9rem" }}>
            {utilisateur?.nom}
          </span>
          <button type="button" className="btn-logout" onClick={handleDeconnexion}>
            Déconnexion
          </button>
        </nav>
      </div>
    </header>
  );
}
