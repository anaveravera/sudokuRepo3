# Opcion 3: GitHub + Actions

## Flujo

1. Crear issue equivalente a `SUM-1` en GitHub.
2. Rama `feature/SUM-1-sudoku-base`.
3. Pull Request a `main`.
4. Workflow `.github/workflows/ci.yml` corre tests.

## Comandos locales

```bash
composer install
composer test
php -S localhost:8080 -t public
```
