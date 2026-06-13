# 📂 DIRECTIVES ET LIVRABLES - PROJET CDA
**Nom du Projet :** NautiLog  
**Concept :** Carnet de navigation électronique intelligent, gestion de flotte et carte interactive de disponibilité.  
**Date de création :** 12 Juin 2026  
**Mentor :** Qwen3.7  

---

## 📅 CALENDRIER IMPÉRATIF DES RENDUS
- **Dimanche 23 Août 2026 (23h59) :** Dépôt numérique (Dossier de Projet + Dossier Professionnel).
- **Vendredi 11 Septembre 2026 (13h00) :** Dépôt papier (2 exemplaires de chaque dossier).
- **Dimanche 20 Septembre 2026 (23h59) :** Dépôt du support de présentation orale (PowerPoint/Canva).
- **Date de soutenance :** À confirmer (Prévoir une démo live + vidéo de secours).

---

## 🏛️ ARCHITECTURE LOGICIELLE (Multi-couches & Sécurisée)

Le projet suit une architecture **Headless (API-First)** strictement séparée en 3 couches, conformément au CCP2 :

1. **Couche de Présentation (Frontend - React) :** 
   - Responsable de l'UI/UX, de l'accessibilité (RGAA) et de l'appel aux API.
   - Ne contient **aucune** logique métier sensible.
   - Gère l'état global (Auth, Réservation) via Context API ou Zustand.
2. **Couche Applicative & Métier (Backend - Symfony) :** 
   - Expose une API REST stateless.
   - Utilise le **Style Défensif** : Toutes les données entrantes passent par des **DTO** (Data Transfer Objects) et sont validées par le composant `Validator` avant d'atteindre la logique métier.
   - La sécurité est gérée par JWT (LexikJWT) et des Voters Symfony pour les droits d'accès fins (RBAC).
3. **Couche de Données (PostgreSQL) :** 
   - Base relationnelle avec contraintes d'intégrité fortes (Foreign Keys, Unique).
   - Les données sensibles (ex: numéro de permis) sont chiffrées au repos (Doctrine Encryption).

**Flux de Sécurité :** Client → (HTTPS) → Nginx → Symfony (Validation DTO + Auth JWT) → Doctrine (Requête préparée) → PostgreSQL.

---

## 📁 ARBORESCENCE DU PROJET (Monorepo)

Une structure monorepo est choisie pour simplifier le CI/CD et le déploiement Docker en 10 semaines, tout en gardant une séparation logique stricte.

```text
nautilog-fleet/
├── .github/
│   └── workflows/
│       └── ci-cd.yml             # Pipeline : Lint -> Tests -> Trivy Scan -> Build
├── docs/                         # 📝 LIVRABLES DOCUMENTAIRES (À alimenter au fur et à mesure)
│   ├── diagrams/                 # MCD, Diagramme de déploiement, Cas d'utilisation (UML)
│   ├── screenshots/              # Captures d'écran pour le dossier de projet
│   └── decisions.md              # Journal des choix techniques (ADR)
├── backend/                      # 🛡️ API SYMFONY (Couche Métier & Données)
│   ├── config/
│   ├── public/
│   ├── src/
│   │   ├── Controller/           # Points d'entrée API (légers, délèguent aux Services)
│   │   ├── Dto/                  # ⚠️ STYLE DÉFENSIF : Objets de transfert de données
│   │   ├── Entity/               # Modèles Doctrine (avec attributs de validation)
│   │   ├── Repository/           # Requêtes SQL personnalisées (optimisées)
│   │   ├── Security/             # Voters, Authenticators, Chiffrement
│   │   ├── Service/              # Logique métier pure (ex: RecommendationEngine, PdfGenerator)
│   │   └── Validator/            # Contraintes de validation personnalisées
│   ├── tests/                    # Tests unitaires (PHPUnit) et d'intégration
│   ├── Dockerfile
│   └── composer.json
├── frontend/                     # 🎨 APPLICATION REACT (Couche Présentation)
│   ├── public/
│   ├── src/
│   │   ├── assets/               # Images optimisées (WebP), icônes
│   │   ├── components/           # Composants réutilisables (Boutons, Cartes, Modales)
│   │   ├── context/              # Gestion d'état global (AuthContext, MapContext)
│   │   ├── hooks/                # Hooks personnalisés (ex: useFetch, useGeoLocation)
│   │   ├── pages/                # Vues principales (Home, MapView, Dashboard, LogBook)
│   │   ├── services/             # Appels API centralisés (Axios avec intercepteurs JWT)
│   │   └── App.jsx               # Point d'entrée de l'application
│   ├── Dockerfile
│   └── package.json
├── docker-compose.yml            # 🐳 Orchestration locale (api, client, db, nginx, mailhog)
├── .gitignore
├── README.md                     # 📖 Documentation technique et instructions d'installation
└── DIRECTIVES_PROJET.md          # Ce fichier