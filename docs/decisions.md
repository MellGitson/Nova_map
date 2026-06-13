# Journal des Décisions Techniques (ADR)

Ce fichier documente les choix d'architecture importants du projet NautiLog.
Format inspiré des Architecture Decision Records (ADR).

---

## ADR-001 : Architecture Headless (API-First)

**Date :** 2026-06-12  
**Statut :** Accepté

**Contexte :** Le projet doit être maintenable, testable et conforme au CCP2.

**Décision :** Séparation stricte en 3 couches : Présentation (React) / Métier (Symfony) / Données (PostgreSQL). Le backend expose uniquement une API REST stateless, le frontend ne contient aucune logique métier.

**Conséquences :** Indépendance des couches, scalabilité, tests unitaires facilités. Complexité initiale plus élevée.

---

## ADR-002 : Authentification JWT (LexikJWT)

**Date :** 2026-06-12  
**Statut :** Accepté

**Contexte :** L'API doit être stateless et sécurisée.

**Décision :** Utilisation de `lexik/jwt-authentication-bundle` pour générer et valider les tokens JWT. Tokens stockés en mémoire côté frontend (pas de localStorage pour éviter XSS).

**Conséquences :** Authentification sans session serveur. Nécessite une gestion du refresh token.

---

## ADR-003 : Style Défensif avec DTOs

**Date :** 2026-06-12  
**Statut :** Accepté

**Contexte :** Sécurité et robustesse des données entrantes.

**Décision :** Toutes les données entrantes transitent par des DTOs validés avec le composant `Validator` de Symfony avant d'atteindre la logique métier. Aucune entité Doctrine n'est exposée directement.

**Conséquences :** Surface d'attaque réduite, validation centralisée, séparation claire entre les données réseau et le domaine.

---

## ADR-004 : Base de données PostgreSQL

**Date :** 2026-06-12  
**Statut :** Accepté

**Contexte :** Besoin d'une base relationnelle robuste avec contraintes d'intégrité.

**Décision :** PostgreSQL 16 pour les types géographiques natifs (PostGIS optionnel), les Foreign Keys et les contraintes Unique. Doctrine ORM comme abstraction.

**Conséquences :** Meilleure intégrité des données. Requiert une image Docker PostgreSQL en développement.

---

## ADR-005 : Monorepo

**Date :** 2026-06-12  
**Statut :** Accepté

**Contexte :** Projet en 10 semaines, équipe réduite.

**Décision :** Dépôt unique contenant Backend, Frontend et configuration Docker. Simplifie le CI/CD et la coordination entre les couches.

**Conséquences :** Déploiements atomiques plus simples. Moins adapté si l'équipe grandit et que les cycles de release divergent.
