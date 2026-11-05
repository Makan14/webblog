# Webblog — Blog Symfony sur le football brésilien

Webblog est un blog MVC construit avec Symfony 7.4 et PHP 8.2, consacré à trois joueurs brésiliens : **Ronaldinho, Neymar et Robinho**.
Thème éditorial : **« Qui est le meilleur joueur brésilien ? »**

- Démo en ligne : <https://webblog-makan.alwaysdata.net>
- Dépôt : <https://github.com/Makan14/webblog>
- Administration : `/connexion` (le bouton de connexion est volontairement masqué pour les visiteurs)

## Fonctionnalités

- Architecture MVC : contrôleurs, entités Doctrine, templates Twig.
- CRUD complet des articles : titre, joueur, extrait, contenu, date, slug.
- CRUD des images : téléversement, texte alternatif, image principale, suppression.
- Compte administrateur unique : la première inscription crée le compte, puis les inscriptions sont bloquées.
- Connexion, déconnexion, suppression du compte avec confirmation par mot de passe.
- Base MySQL / MariaDB via Doctrine ORM et migrations.
- Traductions de l’interface : français, anglais, espagnol, portugais.
- Thème sombre / clair mémorisé dans le navigateur.
- Police Poppins locale, sans dépendance à un CDN.
- Design responsive : mobile, tablette, ordinateur.
- Cartes d’articles avec ombre au survol et ouverture dans le même onglet.
- Footer : « Webblog Copyright 2026 ».
- Commentaires en français dans le code.

## Stack technique

| Élément | Choix |
|---|---|
| Framework | Symfony 7.4 LTS, correctifs de sécurité 7.4.23 |
| PHP | 8.2+ |
| Base de données | MySQL / MariaDB |
| ORM | Doctrine ORM + migrations |
| Templates | Twig |
| CSS | Variables `:root`, thème sombre, responsive |
| Tests | PHPUnit + parcours navigateur Playwright |
| Hébergement de démonstration | alwaysdata Free, HTTPS forcé |

## Prérequis

- PHP 8.2 ou plus récent.
- Composer 2.
- MySQL / MariaDB, par exemple via XAMPP.
- Extensions PHP : `ctype`, `iconv`, `pdo_mysql`, `intl`, `fileinfo`, `mbstring`, `dom`, `xml`, `xmlwriter`, `tokenizer`, `openssl`, `zip`.

## Démarrage rapide

Pour Windows avec XAMPP, voir `docs/INSTALLATION_WINDOWS.md`.

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
Les fixtures créent les articles de démonstration, mais aucun compte : le premier inscrit devient administrateur.

## Déploiement

Le guide complet pour alwaysdata Free est disponible ici :

```text
docs/DEPLOIEMENT_ALWAYSDATA.md
```

Il couvre :

- la configuration PHP et MariaDB ;
- le clone SSH du dépôt ;
- la création privée de `.env.local` ;
- l’installation Composer sans les outils de développement ;
- l’export/import de la base locale pour conserver le compte admin et les articles ;
- la racine Web `public/` et le HTTPS forcé ;
- les vérifications de sécurité avant mise en ligne.

Assistant et vérification inclus :

```bash
php deploy/alwaysdata/configure.php
php deploy/alwaysdata/check.php
```

## Tests

```bash
php bin/phpunit
```

Les tests fonctionnels couvrent notamment l’accueil, les articles, les langues, l’inscription unique, la connexion, le CRUD article/image et la suppression du compte.

## Structure

```text
webblog/
├── config/                 # Configuration Symfony, sécurité, Doctrine, traductions
├── deploy/alwaysdata/      # Assistant de configuration et vérification production
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
│   └── DataFixtures/       # Données de démonstration, dev/test uniquement
├── templates/              # Vues Twig publiques et administration
├── tests/                  # Tests fonctionnels PHPUnit
└── translations/           # Messages et validateurs en 4 langues
```

## Sécurité

- Mots de passe hachés automatiquement.
- CSRF sur les formulaires et suppressions.
- Accès `/admin` réservé à `ROLE_ADMIN`.
- Upload limité aux images JPEG, PNG, WebP de 5 Mo maximum, avec contrôle MIME.
- Un seul compte possible tant qu’il n’est pas supprimé.
- Bouton de connexion masqué pour les visiteurs ; l’admin utilise l’URL directe `/connexion`.
- En production : `APP_DEBUG=0`, HTTPS forcé, proxy alwaysdata configuré.
- Ne jamais committer `.env.local`, un export SQL ou un secret de production.

## Documentation

- `docs/INSTALLATION_WINDOWS.md` : installation locale avec XAMPP.
- `docs/DEPLOIEMENT.md` : principes généraux de déploiement.
- `docs/DEPLOIEMENT_ALWAYSDATA.md` : mise en ligne gratuite sur alwaysdata.
- `docs/VERIFICATIONS.md` : vérifications effectuées avant livraison et production.

## Licence et images

Projet personnel fourni à des fins d’apprentissage et de démonstration.
Les images de joueurs restent la propriété de leurs ayants droit respectifs.