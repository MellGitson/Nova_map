import { Navigate, useLocation } from "react-router-dom";
import { useAuth } from "../contexts";

export function ProtectedRoute({ children }: { children: React.ReactNode }) {
  const { utilisateur, chargement } = useAuth();
  const location = useLocation();

  if (chargement) {
    return (
      <div className="page auth-page">
        <p className="text-muted">Chargement…</p>
      </div>
    );
  }

  if (!utilisateur) {
    return <Navigate to="/login" state={{ from: location }} replace />;
  }

  return <>{children}</>;
}
