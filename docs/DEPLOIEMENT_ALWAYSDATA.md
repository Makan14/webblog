# Déployer Webblog sur alwaysdata Free

Guide adapté au dépôt public `https://github.com/Makan14/webblog`, à Symfony 7.4, PHP 8.2+ et MariaDB.

**Statut : préparation uniquement.** Aucun compte alwaysdata n’est créé, aucun achat n’est effectué et aucune mise en ligne n’est réalisée par ce kit. Les mots de passe alwaysdata, MySQL et GitHub restent sur votre ordinateur ou dans le terminal SSH ; ne les envoyez jamais dans le chat et ne les mettez pas dans Git.

## 1. Offre et architecture prévues

L’offre gratuite alwaysdata est destinée à un usage personnel, sans publicité : 0 €/mois, 1 Go SSD, 256 Mo de RAM, 1/4 CPU et 3 jours de sauvegardes. C’est suffisant pour préparer ce petit blog, mais il faut surveiller la mémoire et l’espace disque après l’installation.

Architecture cible :

```text
Visiteur en HTTPS
        |
Proxy alwaysdata + Apache + PHP 8.2
        |
/home/COMPTE/webblog/public/       <-- seule racine exposée au Web
        |
Application Symfony dans /home/COMPTE/webblog/
        |
MariaDB : mysql-COMPTE.alwaysdata.net:3306
Base : COMPTE_webblog
```

- Adresse gratuite prévue : `https://COMPTE.alwaysdata.net`.
- Aucun achat de domaine : `webblog.com` est déjà enregistré par un tiers.
- Le code vient de GitHub ; les dépendances sont installées par Composer sur le serveur.
- Le serveur PHP intégré `php -S localhost:8000` sert uniquement au développement local. Ne l’utilisez pas pour héberger le site public.

Remplacez `COMPTE` par le nom exact de votre compte alwaysdata dans les chemins et adresses. Ne copiez pas les crochets du modèle d’environnement tels quels.

## 2. Créer le compte et vérifier les accès

1. Ouvrir `https://www.alwaysdata.com/en/register/?p=2012` et choisir l’offre **Free**.
2. Choisir un nom de compte disponible, par exemple `webblog-makan` si libre. Ce nom détermine l’adresse gratuite et les noms de serveurs.
3. Valider l’adresse email et se connecter à `https://admin.alwaysdata.com/`.
4. Dans **Remote access > SSH/SFTP**, vérifier qu’un utilisateur SSH existe. alwaysdata documente SSH pour tous ses environnements.
5. Noter uniquement le nom du compte et les noms serveur/base/utilisateur. Les mots de passe restent secrets.

## 3. Préparer le dépôt GitHub depuis Windows

Le kit met aussi Symfony à jour vers **7.4.23** : les avis de sécurité publiés le 4 novembre 2026 contre 7.4.22 sont corrigés, tout en restant compatible PHP 8.2.

Le kit ajoute :

- `deploy/alwaysdata/configure.php` : assistant interactif créant `.env.local` privé ;
- `deploy/alwaysdata/check.php` : vérification PHP/extensions/configuration sans afficher de secret ;
- `deploy/alwaysdata/env.local.example` : modèle manuel sans secret ;
- la prise en charge du proxy HTTPS alwaysdata dans Symfony ;
- une migration qui retire la table `messenger_messages` inutilisée ;
- l'exclusion des fixtures du conteneur de production ;
- ce guide.

Si vous avez des modifications locales non commitées, sauvegardez-les avant d'appliquer le kit. Ensuite, depuis PowerShell, extraire le ZIP dans le projet puis mettre les dépendances locales en phase avec le nouveau `composer.lock`, vérifier et publier :

```powershell
cd C:\xampp\htdocs\webblog
Expand-Archive -Path "$env:USERPROFILE\Downloads\webblog-preparation-alwaysdata.zip" -DestinationPath "C:\xampp\htdocs\webblog" -Force
composer install
git status --short
git add .env composer.json composer.lock config deploy docs README.md src
git commit -m "Préparer le déploiement alwaysdata"
git push
```

Si vous avez ajouté de nouvelles images localement, inclure aussi `public/uploads/articles/` dans le commit. Ne jamais ajouter `.env.local`, un export SQL ou une sauvegarde contenant des comptes.

Vérification obligatoire :

```powershell
git ls-files .env.local
```

La commande ne doit rien afficher.

## 4. Configurer PHP, MariaDB et le site dans alwaysdata

### PHP au niveau du compte

Dans **Environment > PHP** :

1. Choisir PHP **8.2** comme version par défaut, pour correspondre au `composer.lock` actuel. PHP 8.3/8.4 peuvent aussi convenir, mais 8.2 est la référence testée ici.
2. Vérifier les extensions `intl`, `pdo_mysql`, `fileinfo`, `mbstring`, `dom/xml`, `openssl`, `ctype`, `iconv`, `zip`.
3. Si `intl` n’est pas chargé, ajouter cette ligne dans les paramètres php.ini du compte :

```ini
extension = intl.so
```

Les dates des articles utilisent le formatage international ; `intl` est nécessaire. Ne pas augmenter aveuglément `memory_limit` au-delà de la RAM de l’offre gratuite.

### Base MariaDB

Dans **Databases > MySQL** :

1. Choisir de préférence MariaDB **11.4** si l’interface le propose. Les versions documentées sont 10.11, 11.4 et 11.8 ; l’assistant accepte les trois.
2. Créer une base nommée `COMPTE_webblog`.
3. Utiliser l’utilisateur MySQL par défaut du compte ou créer un utilisateur dédié, avec les droits complets sur cette seule base.
4. Noter le nom exact de la base et de l’utilisateur. Le serveur aura la forme `mysql-COMPTE.alwaysdata.net`, port `3306`.

### Site Web

Dans **Web > Sites**, modifier le site par défaut ou ajouter un site :

- Adresse : `COMPTE.alwaysdata.net` ;
- Type : **PHP** ;
- Version PHP : version par défaut du compte ;
- Root directory : `/home/COMPTE/webblog/public` ;
- SSL : activer **Force HTTPS** après avoir vérifié que le site répond.

La racine doit impérativement se terminer par `/public`. Exposer la racine du projet rendrait `.env.local`, `composer.lock` et le code source accessibles. Le dossier `public` sera créé par le clone GitHub ; l’assistant alwaysdata ne le crée pas automatiquement.

## 5. Se connecter en SSH et récupérer le code

Dans PowerShell sur Windows :

```powershell
ssh COMPTE@ssh-COMPTE.alwaysdata.net
```

Remplacer les deux `COMPTE`. Vérifier l’empreinte du serveur affichée dans le panneau alwaysdata avant d’accepter la première connexion. Si SSH est bloqué par le réseau, alwaysdata propose aussi une interface web `https://ssh-COMPTE.alwaysdata.net`, moins fiable qu’un vrai client SSH.

Une fois connecté :

```bash
php -v
php -m
composer --version
git --version
cd ~
git clone https://github.com/Makan14/webblog.git webblog
cd ~/webblog
```

Le dépôt est public : aucun jeton GitHub n’est nécessaire pour le cloner. Si le dossier existe déjà, ne pas le supprimer ; utiliser `cd ~/webblog && git pull --ff-only` après avoir vérifié l’état local.

## 6. Créer la configuration privée de production

Toujours en SSH :

```bash
php deploy/alwaysdata/configure.php
php deploy/alwaysdata/check.php
```

L’assistant demande le compte, l’adresse du site, la base, l’utilisateur MySQL, la version MariaDB et le mot de passe MySQL. La saisie du mot de passe est masquée dans un terminal SSH normal. Il écrit `.env.local` avec :

- `APP_ENV=prod` et `APP_DEBUG=0` ;
- un `APP_SECRET` neuf de 64 caractères hexadécimaux ;
- l’URL MySQL alwaysdata encodée correctement ;
- `DEFAULT_URI` en HTTPS ;
- les proxys approuvés pour reconnaître HTTPS derrière alwaysdata.

Le fichier est créé avec les permissions `0600` et reste ignoré par Git. Ne pas réutiliser le secret local. Ne pas afficher le fichier avec `cat .env.local` dans une capture d’écran.

Si vous préférez la méthode manuelle, copier `deploy/alwaysdata/env.local.example` vers `.env.local`, remplacer tous les crochets, encoder les caractères spéciaux du mot de passe pour une URL, puis exécuter `chmod 600 .env.local`. L’assistant évite ces erreurs.

## 7. Installer les dépendances de production

```bash
composer install --no-dev --optimize-autoloader --no-scripts
php bin/console cache:clear --env=prod --no-debug
php deploy/alwaysdata/check.php
```

`--no-scripts` évite de lancer le cache Symfony avant que l’environnement et les dépendances soient prêts. Les outils de développement et les fixtures ne sont pas installés en production. Ne pas lancer `doctrine:fixtures:load` sur le serveur public.

Tester la connexion MySQL sans modifier les données :

```bash
php bin/console doctrine:query:sql "SELECT 1" --env=prod
```

## 8. Choisir les données de départ

### Option A — Reprendre le site local existant, recommandée ici

Votre compte administrateur et vos articles sont dans la base MySQL de XAMPP, pas dans GitHub. Pour les retrouver en ligne, transférer la base et les images.

#### Exporter la base locale

Sur Windows, avec MySQL de XAMPP démarré :

```powershell
& "C:\xampp\mysql\bin\mysqldump.exe" --host=127.0.0.1 --port=3306 --user=root --password --single-transaction --quick --skip-lock-tables --no-tablespaces --default-character-set=utf8mb4 --result-file="$env:USERPROFILE\Documents\webblog-local.sql" webblog
```

Si le mot de passe root de XAMPP est vide, appuyer simplement sur Entrée à la demande de mot de passe. Le fichier est écrit dans `Documents`, hors du dépôt Git. Il contient des données privées, dont l’email et l’empreinte du mot de passe administrateur : ne pas le publier.

#### Importer dans la base alwaysdata vide

1. Ouvrir `https://phpmyadmin.alwaysdata.com/`.
2. Se connecter avec le serveur `mysql-COMPTE.alwaysdata.net`, l’utilisateur MySQL et son mot de passe.
3. Sélectionner la base `COMPTE_webblog` **vide**.
4. Utiliser **Importer** et choisir `webblog-local.sql`.
5. Vérifier la présence des tables `article`, `article_image`, `utilisateur` et `doctrine_migration_versions`.

N’importez pas cet export dans une base contenant déjà des données importantes sans sauvegarde préalable.

#### Vérifier les images

Les neuf images initiales sont déjà dans GitHub. Si vous avez ajouté d’autres images après le dernier push, ajoutez-les au dépôt depuis Windows puis faites `git pull --ff-only` en SSH, ou transférez-les par SFTP dans :

```text
/home/COMPTE/webblog/public/uploads/articles/
```

Les noms de fichiers doivent correspondre exactement à ceux enregistrés dans la table `article_image`.

#### Vérifier les migrations

```bash
php bin/console doctrine:migrations:status --env=prod
php bin/console doctrine:schema:validate --env=prod
```

Le schéma doit être synchronisé. Si une migration plus récente que l’export apparaît, l’exécuter :

```bash
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
```

Votre compte administrateur local existe déjà en ligne : `/inscription` doit rester fermé. Connectez-vous avec les mêmes identifiants via `/connexion`.

### Option B — Repartir d’une base vide

Si vous ne voulez pas transférer la base locale :

```bash
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
```

Puis ouvrir `https://COMPTE.alwaysdata.net/inscription`, créer l’unique compte administrateur et ajouter les articles/images depuis l’administration. Les fixtures de démonstration ne sont pas disponibles avec `--no-dev` ; ne pas les charger en production.

## 9. Vérifications avant d’annoncer le site

Dans un navigateur ou avec `curl` :

1. `https://COMPTE.alwaysdata.net/` répond en HTTPS avec les trois cartes si la base de démonstration a été transférée.
2. Un article s’ouvre dans le même onglet, avec son texte et ses images.
3. FR/EN/ES/PT, thème sombre/clair et affichage mobile fonctionnent.
4. `/connexion` permet à l’administrateur de se connecter ; `/admin` redirige un visiteur non connecté.
5. Le bouton Connexion n’apparaît pas pour les visiteurs ; Administration/Déconnexion apparaissent après connexion.
6. Créer, modifier puis supprimer un article de test et une image de test.
7. `https://COMPTE.alwaysdata.net/.env` et `/composer.json` ne doivent jamais renvoyer leur contenu.
8. `http://COMPTE.alwaysdata.net/` redirige vers HTTPS si **Force HTTPS** est activé.
9. Aucune barre de débogage Symfony ni page d’erreur détaillée n’est visible.
10. Le footer affiche `Webblog Copyright 2026`.

Commandes utiles depuis votre PC :

```powershell
curl.exe -I https://COMPTE.alwaysdata.net/
curl.exe -I https://COMPTE.alwaysdata.net/.env
curl.exe -I https://COMPTE.alwaysdata.net/admin
```

## 10. Publier une mise à jour plus tard

Sur Windows : modifier, tester localement, puis `git add`, `git commit`, `git push`.

En SSH sur alwaysdata :

```bash
cd ~/webblog
git pull --ff-only
composer install --no-dev --optimize-autoloader --no-scripts
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
php bin/console cache:clear --env=prod --no-debug
php deploy/alwaysdata/check.php
```

Ne pas écraser `.env.local` ni le dossier `public/uploads/articles/`. Ne pas utiliser `git reset --hard` sur le serveur sans sauvegarde.

## 11. Sauvegardes et limites

- L’offre Free ne conserve que 3 jours de sauvegardes automatiques. Exporter régulièrement la base et les images ailleurs.
- Surveiller l’espace disque après `composer install` et l’usage mémoire dans le panneau.
- Une offre gratuite personnelle n’est pas une garantie de disponibilité contractuelle ; garder une sauvegarde permettant de redéployer ailleurs.
- Les journaux sont dans `/home/COMPTE/admin/logs/http/`, `/home/COMPTE/admin/logs/sites/`, `/home/COMPTE/admin/logs/php/` et `~/webblog/var/log/prod.log`.

## 12. Dépannage rapide

| Symptôme | Vérification |
|---|---|
| 403 ou page vide | Root directory exactement `/home/COMPTE/webblog/public`, dossier créé par le clone |
| 500 | Lire `var/log/prod.log` et les journaux HTTP/PHP ; vérifier `.env.local` et le cache |
| `Access denied` MySQL | Utilisateur/mot de passe/base exacts dans le panneau MySQL ; ne pas utiliser `root` |
| `Connection refused` MySQL | Hôte `mysql-COMPTE.alwaysdata.net`, port 3306, service MariaDB actif |
| Classe introuvable | Refaire `composer install --no-dev --optimize-autoloader --no-scripts` |
| Erreur `IntlDateFormatter` | Charger `extension = intl.so` dans Environment > PHP |
| Images absentes | Vérifier `public/uploads/articles/` et les noms dans `article_image` |
| Upload impossible | Permissions du dossier uploads ; limites PHP et quota disque |
| HTTPS non reconnu par Symfony | `TRUSTED_PROXIES="127.0.0.1,REMOTE_ADDR"` dans `.env.local`, puis `cache:clear --env=prod` ; le cache doit être reconstruit après ce changement |
| Mémoire dépassée | Vérifier les processus et la RAM ; ne pas installer les dépendances dev en production |

## Sources officielles consultées

- Offres : https://www.alwaysdata.com/en/offers/
- SSH : https://help.alwaysdata.com/en/docs/web-hosting/remote-access/ssh/
- PHP et versions : https://help.alwaysdata.com/en/docs/web-hosting/languages/php/configuration/
- Extensions PHP : https://help.alwaysdata.com/en/docs/web-hosting/languages/php/extensions/
- Composer préinstallé : https://help.alwaysdata.com/en/docs/web-hosting/languages/php/packages/
- Sites et racine web : https://help.alwaysdata.com/en/docs/web-hosting/sites/add-a-site/
- MariaDB/MySQL et phpMyAdmin : https://help.alwaysdata.com/en/docs/web-hosting/databases/mariadb/
- Proxy et X-Forwarded-Proto : https://help.alwaysdata.com/en/docs/web-hosting/sites/http-stack/
- Forcer HTTPS : https://help.alwaysdata.com/en/docs/web-hosting/sites/ssl-tls/redirect-http-to-https/
- Symfony derrière un proxy : https://symfony.com/doc/current/deployment/proxies.html
