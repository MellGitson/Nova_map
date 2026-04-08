import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useState,
  type ReactNode,
} from "react";
import type { Utilisateur } from "../types";
import { moi, deconnecter, rafraichirToken } from "../api";

type ValeurContexteAuth = {
  utilisateur: Utilisateur | null;
  chargement: boolean;
  setUtilisateur: (utilisateur: Utilisateur | null) => void;
  deconnexion: () => Promise<void>;
};

const ContexteAuth = createContext<ValeurContexteAuth | null>(null);

export function FournisseurAuth({ children }: { children: ReactNode }) {
  const [utilisateur, setUtilisateur] = useState<Utilisateur | null>(null);
  const [chargement, setChargement] = useState(true);

  useEffect(() => {
    // Tenter de renouveler le access token via le refresh token (cookie httpOnly)
    // puis récupérer le profil. Si les deux échouent, l'utilisateur n'est pas connecté.
    rafraichirToken()
      .then(() => moi())
      .then(setUtilisateur)
      .catch(() => setUtilisateur(null))
      .finally(() => setChargement(false));
  }, []);

  const deconnexion = useCallback(async () => {
    await deconnecter();
    setUtilisateur(null);
  }, []);

  return (
    <ContexteAuth.Provider
      value={{ utilisateur, chargement, setUtilisateur, deconnexion }}
    >
      {children}
    </ContexteAuth.Provider>
  );
}

export function useAuth(): ValeurContexteAuth {
  const ctx = useContext(ContexteAuth);
  if (!ctx) throw new Error("useAuth doit être utilisé dans FournisseurAuth");
  return ctx;
}
