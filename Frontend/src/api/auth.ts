import type { Utilisateur } from "../types";
import { requete, setAccessToken } from "./client";

export type { Utilisateur };

// ---------------------------------------------------------------------------
// Inscription
// ---------------------------------------------------------------------------

export type DonneesInscription = {
  email: string;
  password: string;
  nom: string;
  consentement_rgpd: true;
};

export async function inscrire(donnees: DonneesInscription): Promise<Utilisateur> {
  return requete<Utilisateur>("/api/auth/register", {
    method: "POST",
    body: JSON.stringify(donnees),
  });
}

// ---------------------------------------------------------------------------
// Connexion
// ---------------------------------------------------------------------------

export type DonneesConnexion = {
  email: string;
  password: string;
};

type ReponseConnexion = {
  access_token: string;
  user: Utilisateur;
};

export async function connecter(donnees: DonneesConnexion): Promise<Utilisateur> {
  const reponse = await requete<ReponseConnexion>("/api/auth/login", {
    method: "POST",
    body: JSON.stringify(donnees),
  });

  // Stocker le access token en mémoire pour les prochaines requêtes
  setAccessToken(reponse.access_token);

  return reponse.user;
}

// ---------------------------------------------------------------------------
// Déconnexion
// ---------------------------------------------------------------------------

export async function deconnecter(): Promise<void> {
  await requete("/api/auth/logout", { method: "POST" });
  // Vider le token en mémoire
  setAccessToken(null);
}

// ---------------------------------------------------------------------------
// Profil utilisateur courant
// ---------------------------------------------------------------------------

export async function moi(): Promise<Utilisateur> {
  return requete<Utilisateur>("/api/auth/me");
}

// ---------------------------------------------------------------------------
// Renouvellement du access token via le refresh token (cookie httpOnly)
// ---------------------------------------------------------------------------

type ReponseRafraichissement = {
  access_token: string;
};

export async function rafraichirToken(): Promise<void> {
  const reponse = await requete<ReponseRafraichissement>("/api/auth/refresh", {
    method: "POST",
  });
  setAccessToken(reponse.access_token);
}
