/**
 * Client HTTP — URL de base, gestion du Bearer token en mémoire
 * et appels fetch avec credentials (cookies httpOnly pour le refresh token).
 */

const API_BASE =
  (import.meta.env.VITE_API_URL as string) || "http://localhost:8080";

// ---------------------------------------------------------------------------
// Stockage du access token en mémoire (jamais dans localStorage)
// ---------------------------------------------------------------------------

let accessToken: string | null = null;

export function setAccessToken(token: string | null): void {
  accessToken = token;
}

export function getAccessToken(): string | null {
  return accessToken;
}

// ---------------------------------------------------------------------------
// Fonction de requête générique
// ---------------------------------------------------------------------------

type ErreurApi = { message?: string; errors?: Record<string, string> };

async function requete<T>(
  chemin: string,
  options: RequestInit = {}
): Promise<T> {
  const url = `${API_BASE}${chemin}`;

  const entetes: Record<string, string> = {
    "Content-Type": "application/json",
    ...(options.headers as Record<string, string>),
  };

  // Injecter le Bearer token si présent en mémoire
  if (accessToken) {
    entetes["Authorization"] = `Bearer ${accessToken}`;
  }

  const res = await fetch(url, {
    ...options,
    headers: entetes,
    credentials: "include", // envoie le cookie refresh_token httpOnly
  });

  // 204 No Content — pas de corps JSON
  if (res.status === 204) {
    return undefined as T;
  }

  const donnees = await res.json().catch(() => ({}));

  if (!res.ok) {
    const erreur = donnees as ErreurApi;
    let message = "Erreur serveur";

    if (typeof erreur?.message === "string") {
      message = erreur.message;
    } else if (erreur?.errors) {
      message = Object.values(erreur.errors).join(", ");
    }

    throw new Error(message);
  }

  return donnees as T;
}

export { API_BASE, requete };
