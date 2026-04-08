/**
 * Types partagés — alignés sur les réponses de l'API Symfony NovaMap.
 */

// ---------------------------------------------------------------------------
// Authentification
// ---------------------------------------------------------------------------

export type Utilisateur = {
  id: string;
  email: string;
  nom: string;
  roles: string[];
  email_verifie: boolean;
  date_creation: string;
};

// ---------------------------------------------------------------------------
// Projets
// ---------------------------------------------------------------------------

export type Projet = {
  id: string;
  nom: string;
  description: string | null;
  score_global: number | null;
  nb_composants: number;
  date_creation: string;
  date_modification: string;
  composants?: ComposantResume[];
};

/** Version allégée du composant utilisée dans la liste d'un projet. */
export type ComposantResume = {
  id: string;
  nom: string;
  type: TypeComposant;
  ip_ou_domaine: string | null;
  score: number | null;
  position_x: number;
  position_y: number;
};

// ---------------------------------------------------------------------------
// Composants
// ---------------------------------------------------------------------------

export type TypeComposant = 'serveur' | 'bdd' | 'api' | 'cdn' | 'cloud';
export type Environnement = 'prod' | 'staging' | 'dev';

export type Composant = {
  id: string;
  nom: string;
  type: TypeComposant;
  ip_ou_domaine: string | null;
  version_logicielle: string | null;
  environnement: Environnement;
  port: number | null;
  score: number | null;
  derniere_analyse: string | null;
  position_x: number;
  position_y: number;
  date_creation: string;
  cves_actives?: CveResume[];
};

export type CveResume = {
  cve_id: string;
  severite: 'LOW' | 'MEDIUM' | 'HIGH' | 'CRITICAL';
  cvss: number;
};

// ---------------------------------------------------------------------------
// Graphe (D3 Force Layout)
// ---------------------------------------------------------------------------

export type NoeudGraphe = ComposantResume & {
  // Propriétés ajoutées par D3 lors de la simulation
  x?: number;
  y?: number;
  fx?: number | null;
  fy?: number | null;
};

export type LienGraphe = {
  id: string;
  source_id: string;
  cible_id: string;
  type_lien: string;
  description: string | null;
  // D3 remplace source_id/cible_id par des références d'objets
  source?: NoeudGraphe | string;
  cible?: NoeudGraphe | string;
};

export type DonneesGraphe = {
  nodes: NoeudGraphe[];
  edges: LienGraphe[];
};

// ---------------------------------------------------------------------------
// Alertes
// ---------------------------------------------------------------------------

export type NiveauSeverite = 'faible' | 'moyenne' | 'critique';
export type TypeAlerte =
  | 'cve_critique'
  | 'score_baisse'
  | 'port_expose'
  | 'ssl_expire';

export type Alerte = {
  id: string;
  type: TypeAlerte;
  severite: NiveauSeverite;
  message: string;
  lue: boolean;
  date_creation: string;
  composant_id: string | null;
  projet_id: string | null;
};
