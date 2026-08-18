#!/bin/bash
# Assemble un projet Laravel complet (squelette + socle "apps-a-la-con") pret a etre pousse
# sur ton propre depot git, pour etre deploye par l'egg docker/egg-laravel-git-deploy.json.
#
# Prerequis sur ta machine : PHP 8.3+, Composer, Git, Node (pour le build des assets).
# Usage : ./assemble-project.sh /chemin/vers/ce/zip/decompresse
set -e

SOCLE_DIR="${1:-.}"
PROJECT_DIR="apps-a-la-con"

if [ ! -f "$SOCLE_DIR/composer.json" ]; then
    echo "Usage: ./assemble-project.sh /chemin/vers/pterodactyl-apps (le dossier decompresse du zip)"
    exit 1
fi

echo "==> Creation du squelette Laravel"
composer create-project laravel/laravel "$PROJECT_DIR" --prefer-dist --no-interaction

echo "==> Fusion des fichiers du socle par-dessus"
rsync -a "$SOCLE_DIR"/ "$PROJECT_DIR"/ --exclude=docker --exclude=README.md --exclude=.env.example
cp "$SOCLE_DIR/.env.example" "$PROJECT_DIR/.env.example"

cd "$PROJECT_DIR"

echo "==> Installation des packages (Breeze, Livewire, Filament, Spatie Permission, Horizon)"
composer require laravel/breeze livewire/livewire filament/filament spatie/laravel-permission laravel/horizon --no-interaction

php artisan breeze:install blade --no-interaction || true
php artisan filament:install --panels --no-interaction || true
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --no-interaction || true

npm install && npm run build

echo "==> N'oublie pas d'enregistrer les providers dans bootstrap/providers.php :"
echo "    App\\Providers\\AppServiceProvider::class,"
echo "    App\\Providers\\DynamicMailServiceProvider::class,"
echo "    App\\Modules\\WeedCount\\WeedCountServiceProvider::class,"
echo "    App\\Modules\\Calendar\\CalendarServiceProvider::class,"
echo "    App\\Modules\\LiveLocation\\LiveLocationServiceProvider::class,"
echo "et d'ajouter le middleware 'app.access' dans bootstrap/app.php (voir README)."
echo ""
echo "==> Une fois ces ajustements faits :"
echo "    cd $PROJECT_DIR"
echo "    git init && git add -A && git commit -m 'Initial commit'"
echo "    git remote add origin https://github.com/TOI/apps-a-la-con.git"
echo "    git push -u origin main"
echo ""
echo "Renseigne ensuite cette URL dans la variable GIT_REPOSITORY de l'egg Pterodactyl."
