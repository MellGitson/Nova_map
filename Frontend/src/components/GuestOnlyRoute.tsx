import { Navigate } from "react-router-dom";
import { useAuth } from "../contexts";

export function GuestOnlyRoute({ children }: { children: React.ReactNode }) {
  const { utilisateur, chargement } = useAuth();

  if (chargement) {
    return (
      <div className="page auth-page">
        <p className="text-muted">Chargement…</p>
      </div>
    );
  }

  if (utilisateur) {
    return <Navigate to="/dashboard" replace />;
  }

  return <>{children}</>;
}
