<?php

declare(strict_types=1);

/*
 * Vérification locale de préparation alwaysdata, sans accès à la base.
 * N’affiche jamais le contenu de .env.local ni les identifiants MySQL.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Ce script doit être exécuté en ligne de commande.');
}

$racine = dirname(__DIR__, 2);
$cible = $racine . '/.env.local';
$erreurs = [];
$avertissements = [];

function webblog_verifier(bool $condition, string $message, array &$erreurs): void
{
    fwrite(STDOUT, ($condition ? '[OK] ' : '[ERREUR] ') . $message . PHP_EOL);
    if (!$condition) {
        $erreurs[] = $message;
    }
}

function webblog_valeur_env(string $contenu, string $cle): ?string
{
    if (preg_match('/^' . preg_quote($cle, '/') . '=(.*)$/m', $contenu, $m) !== 1) {
        return null;
    }
    $valeur = trim($m[1]);
    if (strlen($valeur) >= 2 && (($valeur[0] === '"' && str_ends_with($valeur, '"')) || ($valeur[0] === "'" && str_ends_with($valeur, "'")))) {
        $valeur = substr($valeur, 1, -1);
    }
    return $valeur;
}

fwrite(STDOUT, 'Webblog — vérification de préparation alwaysdata' . PHP_EOL . PHP_EOL);

webblog_verifier(PHP_VERSION_ID >= 80200, 'PHP 8.2 ou plus récent (' . PHP_VERSION . ')', $erreurs);

$extensions = ['ctype', 'iconv', 'pdo_mysql', 'intl', 'fileinfo', 'mbstring', 'dom', 'xml', 'xmlwriter', 'tokenizer', 'openssl'];
foreach ($extensions as $extension) {
    webblog_verifier(extension_loaded($extension), 'Extension PHP ' . $extension, $erreurs);
}

webblog_verifier(is_file($racine . '/composer.lock'), 'composer.lock présent', $erreurs);
webblog_verifier(is_file($racine . '/public/index.php'), 'Frontal public/index.php présent', $erreurs);
webblog_verifier(is_file($racine . '/public/.htaccess'), 'Règles Apache public/.htaccess présentes', $erreurs);
webblog_verifier(is_file($cible), 'Fichier privé .env.local présent', $erreurs);

if (is_file($cible)) {
    $contenu = file_get_contents($cible);
    if ($contenu === false) {
        $erreurs[] = 'Lecture de .env.local impossible.';
    } else {
        $env = webblog_valeur_env($contenu, 'APP_ENV');
        $debug = strtolower((string) webblog_valeur_env($contenu, 'APP_DEBUG'));
        $secret = (string) webblog_valeur_env($contenu, 'APP_SECRET');
        $database = (string) webblog_valeur_env($contenu, 'DATABASE_URL');
        $uri = (string) webblog_valeur_env($contenu, 'DEFAULT_URI');
        $proxies = (string) webblog_valeur_env($contenu, 'TRUSTED_PROXIES');

        webblog_verifier($env === 'prod', 'APP_ENV=prod', $erreurs);
        webblog_verifier(in_array($debug, ['0', 'false', 'off', ''], true), 'APP_DEBUG désactivé', $erreurs);
        webblog_verifier(preg_match('/^[a-f0-9]{64}$/', $secret) === 1, 'APP_SECRET aléatoire de 64 caractères hexadécimaux', $erreurs);
        webblog_verifier(
            str_starts_with($database, 'mysql://')
            && str_contains($database, '.alwaysdata.net')
            && !str_contains($database, '127.0.0.1')
            && !str_contains($database, 'localhost')
            && !str_contains($database, 'root:@')
            && !str_contains($database, 'CHANGEZ_MOI')
            && !str_contains($database, '['),
            'DATABASE_URL pointe vers MySQL alwaysdata sans modèle non remplacé',
            $erreurs
        );
        webblog_verifier(str_starts_with($uri, 'https://') && !str_contains($uri, 'COMPTE'), 'DEFAULT_URI en HTTPS', $erreurs);
        webblog_verifier(str_contains($proxies, 'REMOTE_ADDR') || str_contains($proxies, '127.0.0.1'), 'Proxy HTTPS alwaysdata configuré', $erreurs);

        if (PHP_OS_FAMILY !== 'Windows') {
            $permissions = fileperms($cible) & 0777;
            webblog_verifier(($permissions & 0077) === 0, 'Permissions de .env.local non ouvertes aux autres utilisateurs', $erreurs);
        }
    }
}

$uploads = $racine . '/public/uploads/articles';
webblog_verifier(is_dir($uploads) && is_writable($uploads), 'Dossier public/uploads/articles accessible en écriture', $erreurs);
webblog_verifier(is_dir($racine . '/var') ? is_writable($racine . '/var') : is_writable($racine), 'Dossier var/ existant ou pouvant être créé', $erreurs);

if (!is_dir($racine . '/vendor')) {
    $avertissements[] = 'vendor/ absent : lancez composer install --no-dev --optimize-autoloader --no-scripts.';
}
if (!is_file($racine . '/var/cache/prod/App_KernelProdContainer.php')) {
    $avertissements[] = 'Cache prod non détecté : lancez php bin/console cache:clear --env=prod --no-debug après Composer.';
}

foreach ($avertissements as $message) {
    fwrite(STDOUT, '[INFO] ' . $message . PHP_EOL);
}

fwrite(STDOUT, PHP_EOL);
if ($erreurs !== []) {
    fwrite(STDERR, count($erreurs) . ' point(s) à corriger avant la mise en ligne.' . PHP_EOL);
    exit(1);
}

fwrite(STDOUT, '[OK] Préparation locale valide. Ce test ne vérifie pas la connexion MySQL ni le site public.' . PHP_EOL);
fwrite(STDOUT, 'Continuez avec les commandes Doctrine et les tests HTTPS du guide.' . PHP_EOL);
exit(0);
