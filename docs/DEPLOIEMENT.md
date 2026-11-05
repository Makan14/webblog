# Préparation au déploiement

Aucun achat ni mise en ligne n’est effectué automatiquement. Ce guide prépare le choix.

**Guide détaillé retenu pour Webblog : [Déploiement alwaysdata Free](DEPLOIEMENT_ALWAYSDATA.md).**
Il contient les réglages du panneau, l’assistant `.env.local`, les commandes SSH, le transfert optionnel de la base XAMPP et la checklist HTTPS.

## Nom de domaine

`webblog.com` est déjà enregistré depuis 1999 et n’est pas disponible à l’achat neuf.
Un domaine `.com` personnalisé est généralement payant. Une adresse gratuite fournie par l’hébergeur est un sous-domaine, par exemple `webblog.alwaysdata.net` ou `webblog.rf.gd`.

## Options gratuites compatibles PHP / MySQL

### alwaysdata

- Offre gratuite personnelle, sans publicité : 0 €/mois, 1 Go SSD, 256 Mo RAM, 1/4 CPU, sauvegardes 3 jours.
- PHP 8.2+, MariaDB, SSH, Composer préinstallé, Apache/.htaccess et SSL.
- Adresse gratuite `COMPTE.alwaysdata.net` ; racine web à pointer vers `public/`.
- Adapté à ce blog léger ; surveiller mémoire et disque après installation.
- Site : https://www.alwaysdata.com/en/offers/

### InfinityFree

- Hébergement gratuit avec PHP 8.4, MySQL/MariaDB, SSL et sous-domaines gratuits.
- Pas de SSH ni Composer en ligne : il faut téléverser le projet avec `vendor/` déjà installé.
- Vérifier les limites de processus et les fonctions PHP désactivées.
- Site : https://www.infinityfree.com/

## Checklist avant production

1. Définir `APP_ENV=prod` et `APP_DEBUG=0`.
2. Définir un `APP_SECRET` fort et unique.
3. Utiliser un utilisateur MySQL dédié, pas `root`.
4. Exposer uniquement le dossier `public/` comme racine web.
5. Installer les dépendances : `composer install --no-dev --optimize-autoloader`.
6. Exécuter les migrations : `php bin/console doctrine:migrations:migrate --no-interaction`.
7. Ne pas charger les fixtures en production si vous avez déjà des données réelles.
8. Vider et réchauffer le cache : `php bin/console cache:clear --env=prod`.
9. Vérifier HTTPS, sauvegardes et permissions du dossier `public/uploads/articles`.
10. Créer le compte administrateur unique via `/inscription`, puis vérifier que les inscriptions sont fermées.

## Commandes de production

```bash
APP_ENV=prod APP_DEBUG=0 composer install --no-dev --optimize-autoloader
APP_ENV=prod php bin/console doctrine:migrations:migrate --no-interaction
APP_ENV=prod php bin/console cache:warmup
```

## Sources vérifiées

- alwaysdata, offres : https://www.alwaysdata.com/en/offers/
- InfinityFree : https://www.infinityfree.com/
- RDAP Verisign pour `webblog.com` : https://rdap.verisign.com/com/v1/domain/webblog.com
