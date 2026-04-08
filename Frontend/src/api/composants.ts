import type { Composant, DonneesGraphe, LienGraphe } from "../types";
import { requete } from "./client";

// ---------------------------------------------------------------------------
// Graphe complet d'un projet (nœuds + liens pour D3)
// ---------------------------------------------------------------------------

export async function obtenirGraphe(projetId: string): Promise<DonneesGraphe> {
  return requete<DonneesGraphe>(`/api/projets/${projetId}/composants`);
}

// ---------------------------------------------------------------------------
// Détail d'un composant (avec ses CVE actives)
// ---------------------------------------------------------------------------

export async function obtenirComposant(
  projetId: string,
  composantId: string
): Promise<Composant> {
  return requete<Composant>(`/api/projets/${projetId}/composants/${composantId}`);
}

// ---------------------------------------------------------------------------
// Créer un composant dans un projet
// ---------------------------------------------------------------------------

export type DonneesCreationComposant = {
  nom: string;
  type: string;
  ip_ou_domaine?: string | null;
  version_logicielle?: string | null;
  environnement?: string;
  port?: number | null;
  position_x?: number;
  position_y?: number;
};

export async function creerComposant(
  projetId: string,
  donnees: DonneesCreationComposant
): Promise<Composant> {
  return requete<Composant>(`/api/projets/${projetId}/composants`, {
    method: "POST",
    body: JSON.stringify(donnees),
  });
}

// ---------------------------------------------------------------------------
// Modifier un composant
// ---------------------------------------------------------------------------

export type DonneesModificationComposant = Partial<DonneesCreationComposant>;

export async function modifierComposant(
  projetId: string,
  composantId: string,
  donnees: DonneesModificationComposant
): Promise<Composant> {
  return requete<Composant>(`/api/projets/${projetId}/composants/${composantId}`, {
    method: "PATCH",
    body: JSON.stringify(donnees),
  });
}

// ---------------------------------------------------------------------------
// Supprimer un composant
// ---------------------------------------------------------------------------

export async function supprimerComposant(
  projetId: string,
  composantId: string
): Promise<void> {
  return requete<void>(`/api/projets/${projetId}/composants/${composantId}`, {
    method: "DELETE",
  });
}

// ---------------------------------------------------------------------------
// Créer un lien entre deux composants
// ---------------------------------------------------------------------------

export type DonneesCreationLien = {
  source_id: string;
  cible_id: string;
  type_lien?: string;
  description?: string | null;
};

export async function creerLien(
  projetId: string,
  donnees: DonneesCreationLien
): Promise<LienGraphe> {
  return requete<LienGraphe>(`/api/projets/${projetId}/composants/liens`, {
    method: "POST",
    body: JSON.stringify(donnees),
  });
}

// ---------------------------------------------------------------------------
// Supprimer un lien
// ---------------------------------------------------------------------------

export async function supprimerLien(
  projetId: string,
  lienId: string
): Promise<void> {
  return requete<void>(`/api/projets/${projetId}/composants/liens/${lienId}`, {
    method: "DELETE",
  });
}
