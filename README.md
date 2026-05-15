# Opcion 3: GitHub + Actions

## Flujo

1. Crear issue equivalente a `SUM-1` en GitHub.
2. Rama `feature/SUM-1-sudoku-base`.
3. Pull Request a `develop`.
4. Workflow `.github/workflows/ci.yml` con etapas tipo Bamboo:
	- `1) Quality Gate`
	- `2) Build + Unit Tests`
	- `3) Package Artifact`
	- `4) Deploy Staging` (push a `develop/release`)
	- `5) Smoke Test Staging`
	- `6) Manual Approval Production` (entorno `production`)
	- `7) Deploy Production` (push a `release/main`)
5. Flujo recomendado: `develop -> release -> main`.

## Activar despliegue real en GitHub

Configura en GitHub (Repo Settings):

1. Secrets
	- `REPO3_RENDER_STAGING_DEPLOY_HOOK`
	- `REPO3_RENDER_PRODUCTION_DEPLOY_HOOK`
2. Variables
	- `REPO3_STAGING_URL` (ej: `https://sudoku-repo3-staging.onrender.com`)
	- `REPO3_PRODUCTION_URL` (ej: `https://sudoku-repo3.onrender.com`)
3. Environments
	- `staging`
	- `production` (con required reviewers para gate manual)

Workflow ejecutable en repo:

- `.github/workflows/ci.yml`

## Comandos locales

```bash
composer install
composer test
php -S localhost:8080 -t public
```
