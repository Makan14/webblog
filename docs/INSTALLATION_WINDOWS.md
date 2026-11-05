# Installation Windows avec XAMPP

## 1. Prérequis

- XAMPP avec PHP 8.2 ou plus récent.
- Composer : https://getcomposer.org/download/
- MySQL / MariaDB démarré depuis le panneau XAMPP.

Vérifier dans PowerShell :

```powershell
php -v
composer --version
```

Si `php` n’est pas reconnu, ajouter le dossier PHP de XAMPP au PATH, par exemple `C:\xampp\php`.

## 2. Installer le projet

```powershell
cd C:\Users\Makan\Desktop\projet web\front_end
# Dézipper webblog.zip ici, puis :
cd webblog
composer install
copy .env.local.example .env.local
```

## 3. Configurer la base

Dans `.env.local`, générer un secret :

```powershell
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```

Copier la valeur dans `APP_SECRET=`.

Avec XAMPP par défaut, l’utilisateur MySQL est souvent `root` sans mot de passe :

```dotenv
DATABASE_URL="mysql://root:@127.0.0.1:3306/webblog?serverVersion=mariadb-10.4.32&charset=utf8mb4"
```

Adapter si vous avez un mot de passe ou une autre version MariaDB.

## 4. Créer la base et les données de démonstration

```powershell
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction
```

Les fixtures créent trois articles et neuf images. Elles ne créent aucun compte.

## 5. Lancer le site

Option simple :

```powershell
php -S localhost:8000 -t public public/router.php
```

Puis ouvrir `http://localhost:8000`.

Option Apache XAMPP : créer un VirtualHost pointant vers le dossier `public/`, avec `AllowOverride All` pour le `.htaccess`.

## 6. Créer votre compte administrateur

1. Ouvrir `http://localhost:8000/inscription`.
2. Renseigner nom, e-mail et mot de passe.
3. Le premier compte devient administrateur.
4. Les inscriptions suivantes sont bloquées.
5. Se connecter via `/connexion`.

## 7. Tests

```powershell
php bin/phpunit
```

La base de test est `webblog_test`, créée automatiquement si nécessaire.
