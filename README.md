# NautiLog — Carnet de Navigation Électronique Intelligent

Projet CDA — Gestion de flotte et carte interactive de disponibilité.

## Stack Technique

| Couche | Technologie |
|--------|-------------|
| Frontend | React + TypeScript + Vite |
| Backend | Symfony 7 + PHP 8.2 + API REST |
| Base de données | MySQL 8.0 via Doctrine ORM |
| Auth | JWT (LexikJWT) |
| Cache | Redis 7 |
| Conteneurisation | Docker + Nginx |
| CI/CD | GitHub Actions |

## Prérequis

- Docker Desktop >= 24
- Node.js >= 20 (développement local Frontend)
- PHP >= 8.2 + Composer (développement local Backend)

## Démarrage rapide (Docker)

```bash
# Cloner le dépôt
git clone <url-du-repo> nautilog-fleet
cd nautilog-fleet

# Copier les variables d'environnement
cp Backend/.env Backend/.env.local
# Éditer Backend/.env.local avec vos valeurs

# Démarrer tous les services
docker compose up -d

# Initialiser la base de données
docker compose exec api php bin/console doctrine:migrations:migrate --no-interaction

# (Optionnel) Charger les fixtures de développement
docker compose exec api php bin/console doctrine:fixtures:load --no-interaction
```

L'application est disponible sur :
- **Frontend** : http://localhost
- **API** : http://localhost/api
- **Mailhog** (emails de dev) : http://localhost:8025

## Développement local (sans Docker)

### Backend

```bash
cd Backend
composer install
cp .env .env.local
# Configurer DATABASE_URL et REDIS_URL dans .env.local
php bin/console doctrine:migrations:migrate
symfony serve
```

### Frontend

```bash
cd Frontend
npm install
npm run dev
```

## Structure du projet

```
nautilog-fleet/
├── .github/workflows/    # Pipeline CI/CD (Lint → Tests → Trivy → Build)
├── docs/
│   ├── diagrams/         # MCD, diagrammes UML
│   ├── screenshots/      # Captures pour le dossier de projet
│   └── decisions.md      # Journal des choix techniques (ADR)
├── Backend/              # API Symfony (PHP 8.2)
│   └── src/
│       ├── Controller/   # Points d'entrée API
│       ├── Dto/          # Objets de transfert (style défensif)
│       ├── Entity/       # Modèles Doctrine
│       ├── Repository/   # Requêtes SQL
│       ├── Security/     # Voters, Authenticators
│       ├── Service/      # Logique métier
│       └── Validator/    # Contraintes personnalisées
├── Frontend/             # Application React (TypeScript)
│   └── src/
│       ├── components/   # Composants réutilisables
│       ├── context/      # État global (Auth, Map)
│       ├── hooks/        # Hooks personnalisés
│       ├── pages/        # Vues principales
│       ├── services/     # Appels API (Axios + JWT)
│       └── views/        # Vues complémentaires
├── docker-compose.yml    # Orchestration locale
└── README.md
```

## Tests

```bash
# Backend (PHPUnit)
docker compose exec api vendor/bin/phpunit

# Frontend (Vitest)
docker compose exec client npm run test
```

## Flux de sécurité

```
Client → (HTTPS) → Nginx → Symfony (DTO + JWT) → Doctrine → MySQL
```

Toutes les données entrantes transitent par des **DTOs validés** avant d'atteindre la logique métier. Les accès sont contrôlés par des **Voters Symfony** (RBAC).

## Calendrier

| Date | Livrable |
|------|----------|
| 23 Août 2026 | Dépôt numérique (Dossier de Projet + Dossier Professionnel) |
| 11 Septembre 2026 | Dépôt papier (2 exemplaires) |
| 20 Septembre 2026 | Support de présentation orale |
| À confirmer | Soutenance (démo live + vidéo de secours) |
