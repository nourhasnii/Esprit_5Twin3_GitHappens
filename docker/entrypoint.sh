#!/bin/sh
# Démarrage du conteneur : base SQLite, migrations, données de démonstration, caches.
set -e
cd /var/www/html

# Apache écoute sur le port donné par Render ($PORT, 10000 par défaut)
sed -i "s/^Listen .*/Listen ${PORT:-10000}/" /etc/apache2/ports.conf

if [ -z "$APP_KEY" ]; then
    echo "ERREUR : la variable APP_KEY est vide (à définir dans Render > Environment)." >&2
    exit 1
fi

DB_FILE="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
export DB_DATABASE="$DB_FILE"
FIRST_START=0
if [ ! -s "$DB_FILE" ]; then
    FIRST_START=1
    mkdir -p "$(dirname "$DB_FILE")"
    touch "$DB_FILE"
fi
chown -R www-data:www-data "$(dirname "$DB_FILE")" storage bootstrap/cache

php artisan migrate --force

# Première mise en route : données de démonstration (comptes, produits, lots, stocks, IA)
if [ "$FIRST_START" = "1" ] && [ "${SEED_DEMO:-true}" = "true" ]; then
    php artisan db:seed --force
    php artisan db:seed --class=StockModuleDemoSeeder --force
fi
chown -R www-data:www-data "$(dirname "$DB_FILE")"

php artisan storage:link --force >/dev/null 2>&1 || true
php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R www-data:www-data storage bootstrap/cache

exec "$@"
