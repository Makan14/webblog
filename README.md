# Webblog — Blog Symfony sur le football brésilien

Webblog est un blog Symfony 7.4 LTS consacré à trois joueurs brésiliens : Ronaldinho, Neymar et Robinho.
Thème éditorial : « Qui est le meilleur joueur brésilien ? »

## Fonctionnalités

- Architecture MVC : contrôleurs, entités Doctrine, templates Twig.
- CRUD complet des articles : titre, joueur, extrait, contenu.
- CRUD des images : téléversement, texte alternatif, image principale, suppression.
- Compte administrateur unique : la première inscription crée le compte, puis les inscriptions sont bloquées.
- Connexion, déconnexion, suppression du compte avec confirmation par mot de passe.
- Base MySQL / MariaDB via Doctrine ORM et migrations.
- Traductions : français, anglais, espagnol, portugais.
- Thème sombre / clair mémorisé dans le navigateur.
- Police Poppins locale, sans dépendance à un CDN.
- Design responsive : mobile, tablette, ordinateur.
- Footer : « Webblog Copyright 2026 ».
- Commentaires en français dans le code.

## Prérequis

- PHP 8.2 ou plus récent (compatible avec le PHP 8.2.12 de XAMPP).
- MySQL / MariaDB (XAMPP convient).
- Extensions PHP : ctype, iconv, pdo_mysql, intl, gd, mbstring, xml, zip (fournies par XAMPP).

## Démarrage rapide

Voir `docs/INSTALLATION_WINDOWS.md` pour XAMPP.

```bash
composer install
cp .env.local.example .env.local
# Adapter DATABASE_URL et APP_SECRET dans .env.local
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction
php -S localhost:8000 -t public public/router.php
```

Ouvrir `http://localhost:8000`, puis `/inscription` pour créer le compte administrateur unique.

## Structure

```text
webblog/
├── config/                 # Configuration Symfony, sécurité, Doctrine, traductions
├── docs/                   # Installation Windows, déploiement, vérifications
├── public/                 # Frontal, CSS, JS, polices, images téléversées
├── resources/images/       # Photos sources des joueurs
├── src/
│   ├── Controller/         # Contrôleurs MVC
│   ├── Entity/             # Entités Doctrine : User, Article, ArticleImage
│   ├── Form/               # Formulaires Symfony
│   ├── Migrations/         # Migrations de base de données
│   ├── Repository/         # Requêtes Doctrine
│   ├── Service/            # Upload, slugs, inscription unique
│   └── DataFixtures/       # Données de démonstration
├── templates/              # Vues Twig publiques et administration
├── tests/                  # Tests fonctionnels PHPUnit
└── translations/           # Messages et validateurs en 4 langues
```

## Sécurité

- Mots de passe hachés automatiquement.
- CSRF sur les formulaires et suppressions.
- Accès `/admin` réservé à `ROLE_ADMIN`.
- Upload limité aux images JPEG, PNG, WebP de 5 Mo maximum.
- Un seul compte possible tant qu’il n’est pas supprimé.
- Ne jamais committer `.env.local` ni de secret de production.

## Licence

Projet fourni pour usage personnel. Isotope n’est pas utilisé ici.
