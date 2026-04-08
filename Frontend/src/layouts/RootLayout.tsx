import { Outlet } from "react-router-dom";
import { useAuth } from "../contexts";
import { VisitorHeader, AuthenticatedHeader } from "../components";

export function RootLayout() {
  const { utilisateur, chargement } = useAuth();

  return (
    <div className="app">
      {!chargement && utilisateur ? (
        <AuthenticatedHeader />
      ) : (
        <VisitorHeader />
      )}
      <main className="main">
        <Outlet />
      </main>
      <footer className="footer">
        <div className="footer-inner">
          <span>NovaMap</span>
          <span className="text-muted">Cartographie de la sécurité infrastructure</span>
        </div>
      </footer>
    </div>
  );
}
