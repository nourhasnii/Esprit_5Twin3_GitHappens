# Déploiement continu (CD) — NutriTrace sur Render

## Le pipeline (`.github/workflows/ci.yml`)

| Job | Quand | Ce qu'il fait |
|---|---|---|
| `build-and-test` | pull request et push sur `main` | Installe PHP 8.2 / Node 20, compile les assets, lance les tests Pest |
| `docker-image` | si les tests passent | Construit l'image Docker de production, la démarre et vérifie `/up`, l'accueil, la connexion et l'arabe (RTL) |
| `deploy` | **seulement après un merge sur `main`**, si les 2 jobs précédents sont verts | Appelle le *Deploy Hook* de Render, puis attend que `/up` réponde 200 |

Une pull request lance donc la CI (tests + image) sans rien déployer ; le merge déploie.

## L'image Docker (`Dockerfile`, dossier `docker/`)

1. `composer` : dépendances PHP sans les paquets de dev.
2. `node` : compilation Tailwind / Vite.
3. `php:8.2-apache` : image finale (pdo_sqlite, gd, zip, opcache).

Au démarrage (`docker/entrypoint.sh`) : création de la base SQLite, `migrate --force`,
données de démonstration au premier démarrage, puis `config:cache`, `route:cache`, `view:cache`.

## Mise en place (une seule fois)

1. Créer un compte sur https://render.com (connexion avec GitHub).
2. **New + → Blueprint** → choisir le dépôt → Render lit `render.yaml` et crée le service `nutritrace`.
3. Renseigner les 3 variables demandées :
   - `APP_KEY` : résultat de `php artisan key:generate --show`
   - `ADMIN_PASSWORD` : mot de passe du compte administrateur de démonstration
   - `APP_URL` : `https://nutritrace-xxxx.onrender.com` (adresse affichée par Render)
4. Render → service → **Settings → Deploy Hook** → copier l'URL.
5. GitHub → **Settings → Secrets and variables → Actions** :
   - onglet *Secrets* : `RENDER_DEPLOY_HOOK_URL` = l'URL du Deploy Hook
   - onglet *Variables* : `RENDER_APP_URL` = l'adresse du site

## Limites de l'offre gratuite

- Le service s'endort après 15 min sans visite (premier chargement ≈ 1 min).
- Le disque n'est pas conservé : la base SQLite est recréée avec les données de démonstration à chaque déploiement.
- Pas d'Ollama : les analyses IA locales (OCR, qualité) restent en attente ; les IA du module Stocks (prévision, optimisation, DLC dynamique, jumeau numérique) fonctionnent.
