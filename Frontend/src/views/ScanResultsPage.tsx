/**
 * Vue : placeholder — redirige vers le dashboard.
 * Anciennement ScanResultsPage (analyse de code SecureScan).
 * Remplacée par les vues NovaMap spécifiques.
 */
import { useEffect } from "react";
import { useNavigate } from "react-router-dom";

export function ScanResultsPage() {
  const navigate = useNavigate();

  useEffect(() => {
    navigate("/dashboard", { replace: true });
  }, [navigate]);

  return null;
}
