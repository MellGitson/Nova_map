import type { Projet } from "../types";
import { requete } from "./client";

// ---------------------------------------------------------------------------
// Liste des projets de l'utilisateur connecté
// ---------------------------------------------------------------------------

export async function listerProjets(): Promise<Projet[]> {
  return requete<Projet[]>("/api/projets");
}

// ---------------------------------------------------------------------------
// Détail d'un projet (avec ses composants)
// ---------------------------------------------------------------------------

export async function obtenirProjet(projetId: string): Promise<Projet> {
  return requete<Projet>(`/api/projets/${projetId}`);
}

// ---------------------------------------------------------------------------
// Créer un nouveau projet
// ---------------------------------------------------------------------------

export type DonneesCreationProjet = {
  nom: string;
  description?: string | null;
};

export async function creerProjet(donnees: DonneesCreationProjet): Promise<Projet> {
  return requete<Projet>("/api/projets", {
    method: "POST",
    body: JSON.stringify(donnees),
  });
}

// ---------------------------------------------------------------------------
// Modifier un projet existant
// ---------------------------------------------------------------------------

export type DonneesModificationProjet = {
  nom?: string;
  description?: string | null;
};

export async function modifierProjet(
  projetId: string,
  donnees: DonneesModificationProjet
): Promise<Projet> {
  return requete<Projet>(`/api/projets/${projetId}`, {
    method: "PATCH",
    body: JSON.stringify(donnees),
  });
}

// ---------------------------------------------------------------------------
// Supprimer un projet
// ---------------------------------------------------------------------------

export async function supprimerProjet(projetId: string): Promise<void> {
  return requete<void>(`/api/projets/${projetId}`, { method: "DELETE" });
}
