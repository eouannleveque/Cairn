# Apps à la con — socle Laravel

Ce zip contient le **socle applicatif** (pas un projet Laravel complet généré) : migrations, modèles,
système de modules, module Weed Count complet, et toute la config Docker/Pterodactyl.
Je n'ai pas accès à `packagist.org` depuis mon environnement pour générer le squelette Laravel
complet (`vendor/`, fichiers de base), donc voici comment assembler le tout chez toi (10 min).

## 1. Créer le squelette Laravel et fusionner ces fichiers

```bash
composer create-project laravel/laravel apps-a-la-con
cd apps-a-la-con

# copie tous les fichiers de ce zip par-dessus (écrase composer.json, .env.example)
# puis :
composer install

composer require laravel/breeze livewire/livewire filament/filament spatie/laravel-permission laravel/horizon

php artisan breeze:install blade
php artisan filament:install --panels
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

## 2. Enregistrer le module Weed Count et le middleware

Dans `bootstrap/providers.php` (Laravel 11), ajoute :

```php
App\Modules\WeedCount\WeedCountServiceProvider::class,
App\Modules\Calendar\CalendarServiceProvider::class,
App\Modules\LiveLocation\LiveLocationServiceProvider::class,
```

Dans `bootstrap/app.php`, section `->withMiddleware(...)`, enregistre l'alias :

```php
$middleware->alias([
    'app.access' => \App\Http\Middleware\CheckAppAccess::class,
]);
```

`App\Providers\AppServiceProvider` (fourni dans ce zip) enregistre déjà `ModuleManager`,
`PointsService` et les composants Livewire du profil — remplace juste le fichier généré par défaut
par celui du zip. Ajoute aussi dans `bootstrap/providers.php` :

```php
App\Providers\DynamicMailServiceProvider::class,
```

## 3. Migrer et seeder

```bash
php artisan migrate
php artisan db:seed --class=Database\\Seeders\\BaseSeeder

php artisan tinker
>>> \Spatie\Permission\Models\Role::create(['name' => 'admin']);
>>> \Spatie\Permission\Models\Role::create(['name' => 'user']);
>>> $admin = App\Models\User::first();
>>> $admin->assignRole('admin');
```

Crée aussi une ressource Filament pour `User` (édition de rôle, reset de points) si tu veux gérer les rôles
depuis l'admin plutôt que tinker — pas encore fait dans ce zip.

## 4. Lancer en local

```bash
docker compose up --build
```

- App : http://localhost:8080
- Mailhog (voir les mails envoyés) : http://localhost:8025

## 5. Déploiement Pterodactyl / Pelican

Trois options, de la plus simple à la plus robuste :

### Option A — Egg "Git deploy" (recommandée pour démarrer, pas de build Docker)

1. Assemble le projet complet en local : `./assemble-project.sh /chemin/vers/pterodactyl-apps`
   (le script génère le squelette Laravel, fusionne le socle, installe Breeze/Livewire/Filament).
2. Termine à la main les 2-3 ajustements affichés en fin de script (enregistrer les providers dans
   `bootstrap/providers.php`, le middleware `app.access` dans `bootstrap/app.php`).
3. `git init && git add -A && git commit -m "init" && git push` vers un repo GitHub (public ou privé).
4. Dans Pterodactyl : Admin → Nests → Import Egg → `docker/egg-laravel-git-deploy.json`.
5. Crée un serveur avec cet egg, renseigne `GIT_REPOSITORY` (l'URL de ton repo, avec un token si privé :
   `https://TOKEN@github.com/toi/repo.git`) et les variables `DB_*`.
6. L'egg clone le repo, fait `composer install`, génère `.env`/`APP_KEY`, et démarre avec
   `php artisan serve`. Aucune image Docker à construire ni à pousser sur un registre.

**Limites de cette option** (compromis pour rester simple) : pas de nginx/php-fpm (juste le serveur
intégré `artisan serve`, largement suffisant pour un usage interne/petit nombre d'utilisateurs), pas
de worker de queue séparé (mails envoyés en synchrone via `QUEUE_CONNECTION=sync`), sessions/cache sur
la base de données plutôt que Redis (évite une dépendance supplémentaire). Pour repasser sur Redis et
un vrai queue worker plus tard, ajoute-les comme services séparés et ajuste le `.env`.

### Option B — Egg Docker custom (image que tu build et push toi-même)

1. Build l'image et pousse-la sur un registre (`ghcr.io/...` par ex) via CI :
   `docker build -f docker/Dockerfile -t ghcr.io/toi/apps-a-la-con:latest .`
2. Admin → Nests → Import Egg → `docker/egg-laravel.json`.
3. Renseigne les variables (`DB_HOST`, `DB_DATABASE`, etc.) pointant vers ta base MySQL.
4. Le conteneur migre automatiquement la DB au démarrage (`entrypoint.sh`), inclut nginx + php-fpm +
   queue worker via supervisord — plus complet mais demande un registre Docker.

### Option C — Pelican Panel

Même principe que l'option B, avec `docker/egg-pelican.json` (schéma `PTDL_v2` identique, import via
Admin → Eggs → Import).

## Ce qui est déjà là

- ✅ Migrations : users étendu, apps, app_user_access, points_ledger, rewards, reward_redemptions, mail_settings, mail_templates
- ✅ Modèles Eloquent + relations
- ✅ Système de modules (`ModuleManager`, `ModuleContract`) pour ajouter des apps proprement
- ✅ Middleware de contrôle d'accès par app (`app.access:slug`)
- ✅ `PointsService` (attribution/dépense de points, traçabilité complète)
- ✅ Module **Weed Count** complet : écran principal (+1, ajout a posteriori, édition, achat de bout)
  + écran de stats (jour/semaine/mois/année, bar/line/pie via Chart.js, moyennes)
- ✅ Module **Calendrier** complet : vue mensuelle, création/édition d'événements (titre, description,
  lieu, journée entière ou horaires), invitation d'utilisateurs **internes uniquement** (recherche
  parmi les comptes de la plateforme, pas d'email externe), acceptation/refus d'invitation par
  l'invité, seul le créateur peut modifier/supprimer l'événement, points configurables sur création
  d'événement et acceptation d'invitation
- ✅ Module **Position en direct** : partage de géolocalisation en temps réel, **opt-in explicite et
  révocable à tout moment** — l'utilisateur choisit nommément qui peut voir sa position (recherche
  interne, jamais de lien public). Utilise `navigator.geolocation.watchPosition` côté navigateur +
  Livewire pour pousser les mises à jour, carte Leaflet/OpenStreetMap (pas de clé API requise),
  rafraîchissement de la carte via `wire:poll.10s`. Désactiver le partage supprime immédiatement la
  dernière position connue en base (pas d'historique de déplacement conservé).
- ✅ Docker (dev + Pterodactyl) prêt à l'emploi
- ✅ **Admin Filament** : `AppModuleResource` (activer apps + gérer l'accès par user + config points),
  `RewardResource` (CRUD des récompenses), `RewardRedemptionResource` (approuver/refuser, remboursement
  auto des points en cas de refus), `MailSettings` (page singleton SMTP), `MailTemplateResource`
  (éditeur avec variables `{{user.name}}` etc.)
- ✅ **Mail dynamique** : `DynamicMailServiceProvider` (lit `mail_settings` en DB au boot) +
  `TemplatedMail` (Mailable générique basé sur `MailTemplate::render()`)
- ✅ **Page perso `/me`** : sélecteur de thème avec 8 palettes pastel en presets (+ personnalisation
  fine RGB), widget de stats agrégées multi-apps, boutique de récompenses avec échange de points
- ✅ **Layout principal** avec navbar générée dynamiquement (uniquement les apps auxquelles
  l'utilisateur a accès) + injection des couleurs de thème en CSS variables

## À faire pour finaliser le socle

1. **Enregistrer les providers** dans `bootstrap/providers.php` :
   `App\Providers\DynamicMailServiceProvider::class` (en plus de `WeedCountServiceProvider`,
   `CalendarServiceProvider`, `LiveLocationServiceProvider` et `AppServiceProvider`, voir §2 plus haut).
2. **Ressource Filament `User`** : gestion des rôles et reset manuel du solde de points depuis l'admin
   (pas encore fait).
3. **Design system Cairn** : le layout utilise des classes Tailwind génériques (`btn-primary`,
   `btn-secondary`) + les CSS variables de thème. À styliser plus finement avec la charte du logo
   (fond sombre `#14170F`, cartes `#1D2116`, bordures `#333826`) si tu veux un habillage plus poussé
   que juste les couleurs perso utilisateur.
4. **Prochaines apps** : créer un nouveau module sur le modèle de `app/Modules/WeedCount` (manifest
   `module.json`, migrations, `ModuleContract`, Livewire, vues), puis l'enregistrer dans
   `bootstrap/providers.php` et via `AppModule::create(['slug' => ...])`.
5. **Position en direct — passage en websockets (optionnel)** : la carte se rafraîchit actuellement
   par polling (`wire:poll.10s`), suffisant pour un usage interne modeste. Pour une vraie latence
   temps réel, remplacer par Laravel Reverb (déjà auto-hébergeable, pas de service tiers) + un event
   broadcast `LocationUpdated` écouté côté client — je peux le faire si le polling s'avère trop lent
   à l'usage.
6. **RGPD / rétention** : `live_locations` ne garde que la dernière position (pas d'historique), et
   `location_shares` est purement opt-in par l'utilisateur qui partage. Si tu veux un log d'audit
   (qui a vu la position de qui, quand), il faudra une table supplémentaire — pas fait par défaut
   pour rester minimal.
