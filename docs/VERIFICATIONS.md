# Vérifications effectuées

## 1. Vérification statique

- `php -l` sur les fichiers PHP du projet.
- `php bin/console lint:yaml config translations`.
- `php bin/console lint:twig templates`.
- `php bin/console lint:container`.
- `composer validate` et `composer audit`.

Résultat : syntaxe valide, conteneur cohérent, aucune faille de sécurité connue signalée par Composer au moment de la vérification.

## 2. Base de données

- Migrations Doctrine générées et exécutées : `Version20261103151027` puis `Version20261105125000`.
- `php bin/console doctrine:schema:validate`.
- Fixtures : 3 articles, 9 images, 0 compte.
- Tables : `article`, `article_image`, `utilisateur`, `doctrine_migration_versions`.
- La table `messenger_messages` a été retirée : aucun transport asynchrone n'est utilisé.

Résultat : schéma synchronisé avec le mapping.

## 3. Tests fonctionnels PHPUnit

Commande : `php bin/phpunit`.

Résultat : 10 tests, 52 assertions, tous validés.

Couverture :
- accueil avec trois cadres ;
- article complet avec texte et galerie ;
- 404 article inconnu ;
- protection de `/admin` ;
- première inscription administrateur puis fermeture ;
- connexion et suppression de compte ;
- refus de suppression avec mauvais mot de passe ;
- CRUD article et image ;
- modification du texte alternatif et de l’image principale ;
- changement de langue FR / EN / ES / PT.

## 4. Parcours navigateur Playwright

Environnement : Chromium, serveur PHP en mode production.

Validé :
- trois cartes cliquables dans le même onglet ;
- ombre au survol ;
- footer exact ;
- Poppins chargée ;
- thème sombre mémorisé ;
- quatre langues ;
- responsive 375, 768, 1280 ;
- inscription unique, connexion, création d’article avec image, modification du texte, modification d’image, suppression d’image, suppression d’article, déconnexion, suppression du compte ;
- aucune erreur JavaScript.

## 5. Vérification production / sécurité

- Cache prod réchauffé.
- Page servie sans barre de débogage Symfony.
- `/admin` redirige vers `/connexion` sans session.
- Upload limité en taille et type MIME.
- CSRF actif sur les formulaires sensibles.
- Aucun secret de production dans le dépôt.
- `composer audit` sans avis de vulnérabilité connu.

## Limites connues

- Les textes des articles sont des données de démonstration Lorem ipsum.
- La traduction automatique du contenu saisi par l’administrateur n’est pas incluse : l’interface est traduite, le contenu reste celui saisi.
- L’hébergement gratuit peut avoir des limites de stockage, de processus ou de fonctions PHP.

## 6. Vérification de compatibilité PHP 8.2 (après retour utilisateur)

Le premier archivage embarquait des dépendances résolues pour PHP 8.4.
L'utilisateur disposant de XAMPP avec PHP 8.2.12, les dépendances ont été
réajustées pour PHP 8.2 :

- `composer config platform.php 8.2.12` puis `composer update`.
- Versions ajustées : doctrine/doctrine-bundle 2.19, doctrine-migrations-bundle 3.7,
  stimulus-bundle et ux-turbo 2.36, PHPUnit 11.5.
- Aucun paquet du fichier `composer.lock` n'exige PHP 8.3 ou 8.4.
- Aucune syntaxe propre à PHP 8.3/8.4 dans `src/`, `config/`, `tests/`.
- `vendor/composer/platform_check.php` accepte PHP >= 8.2.0.

Les cinq vérifications ont été rejouées après ce changement :

1. Statique : php -l, 31 fichiers YAML, 10 templates Twig, conteneur, composer validate/audit : OK.
2. Base de données : migration rejouée sur une base vierge, schéma synchronisé : OK.
3. PHPUnit : 10 tests, 52 assertions : OK.
4. Parcours navigateur complet (production) : OK, aucune erreur JavaScript.
5. Production : pages 200, /admin redirige, aucune barre de débogage : OK.

## 7. Préparation du déploiement alwaysdata Free

Objectif : préparer l'envoi en ligne sans créer de compte ni exécuter le déploiement public.

Correctifs et adaptations inclus :

- Symfony mis à jour de 7.4.22 vers **7.4.23** après les avis de sécurité du 4 novembre 2026 ; `composer audit` ne signale plus d'avis connu.
- Plateforme Composer maintenue à PHP **8.2.12** ; aucun paquet du verrou n'exige PHP 8.3+.
- Proxy HTTPS alwaysdata : `TRUSTED_PROXIES` et `trusted_headers: x-forwarded-proto` uniquement.
- Assistant `deploy/alwaysdata/configure.php` : `.env.local` privé 0600, secret généré, mot de passe encodé pour l'URL, aucun secret affiché.
- Vérification `deploy/alwaysdata/check.php` : PHP/extensions/configuration sans connexion à la base.
- Fixtures exclues du conteneur de production et enregistrées seulement en dev/test.
- Messenger réduit à un bus sans transport ; migration de suppression de `messenger_messages`.

Cinq vérifications rejouées :

1. Statique : `php -l`, 31 YAML, 10 Twig, conteneur prod, `composer validate`, `composer audit` : OK.
2. Base de données : deux migrations exécutées, `doctrine:schema:validate` synchronisé, 0 nouvelle migration : OK.
3. PHPUnit : **10 tests, 52 assertions** : OK.
4. Parcours navigateur Playwright en production : accueil, article, langues, responsive, inscription unique, CRUD article/image, suppression du compte : OK, aucune erreur JavaScript.
5. Production : installation `composer install --no-dev --optimize-autoloader --no-scripts` testée dans une copie isolée, configuration alwaysdata simulée, cache prod réchauffé, conteneur valide ; test local du proxy : avec `X-Forwarded-Proto: https`, `/admin` redirige en `https://.../connexion`, sans l'en-tête il reste en `http://.../connexion`.

Limite : le déploiement réel sur alwaysdata, la connexion à la base distante et l'import phpMyAdmin restent à exécuter par l'utilisateur avec son propre compte.
