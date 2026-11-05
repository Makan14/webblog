<?php

declare(strict_types=1);

/*
 * Assistant de configuration alwaysdata pour Webblog.
 * À exécuter en SSH, depuis la racine du projet, avec PHP 8.2 ou plus récent.
 * Le mot de passe MySQL est écrit uniquement dans .env.local (ignoré par Git).
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Ce script doit être exécuté en ligne de commande.');
}
if (PHP_VERSION_ID < 80200) {
    fwrite(STDERR, "PHP 8.2 ou plus récent est requis. Version actuelle : " . PHP_VERSION . PHP_EOL);
    exit(1);
}

function webblog_question(string $libelle, ?string $defaut = null): string
{
    $suffixe = $defaut !== null ? ' [' . $defaut . ']' : '';
    fwrite(STDOUT, $libelle . $suffixe . ' : ');
    $ligne = fgets(STDIN);
    if ($ligne === false) {
        throw new RuntimeException('Entrée interrompue.');
    }
    $valeur = trim($ligne);
    return $valeur === '' && $defaut !== null ? $defaut : $valeur;
}

function webblog_question_validee(string $libelle, ?string $defaut, string $motif, string $aide): string
{
    while (true) {
        $valeur = webblog_question($libelle, $defaut);
        if (preg_match($motif, $valeur) === 1) {
            return $valeur;
        }
        fwrite(STDOUT, '  Valeur invalide. ' . $aide . PHP_EOL);
    }
}

function webblog_mot_de_passe(): string
{
    $masquage = false;
    $etatTerminal = null;
    $terminal = function_exists('stream_isatty') && @stream_isatty(STDIN);

    if ($terminal && stripos(PHP_OS_FAMILY, 'Windows') !== 0) {
        if (!function_exists('shell_exec')) {
            throw new RuntimeException('Impossible de masquer la saisie. Configurez .env.local avec nano à la place.');
        }
        $etatTerminal = shell_exec('stty -g 2>/dev/null');
        if (is_string($etatTerminal) && trim($etatTerminal) !== '') {
            shell_exec('stty -echo 2>/dev/null');
            $masquage = true;
        } else {
            throw new RuntimeException('Impossible de masquer la saisie. Configurez .env.local avec nano à la place.');
        }
    }

    try {
        $motDePasse = webblog_question('Mot de passe MySQL' . ($masquage ? ' (saisie masquée)' : ''));
    } finally {
        if ($masquage && is_string($etatTerminal)) {
            shell_exec('stty ' . escapeshellarg(trim($etatTerminal)) . ' 2>/dev/null');
            fwrite(STDOUT, PHP_EOL);
        }
    }

    if ($motDePasse === '') {
        throw new RuntimeException('Le mot de passe MySQL ne peut pas être vide.');
    }
    return $motDePasse;
}

try {
    $racine = dirname(__DIR__, 2);
    $cible = $racine . '/.env.local';

    fwrite(STDOUT, PHP_EOL . 'Webblog — configuration de production alwaysdata' . PHP_EOL);
    fwrite(STDOUT, 'Aucune valeur secrète ne sera affichée ni envoyée sur GitHub.' . PHP_EOL . PHP_EOL);

    if (is_file($cible)) {
        $reponse = strtolower(webblog_question('Un fichier .env.local existe déjà. Le remplacer ? (oui/non)', 'non'));
        if (!in_array($reponse, ['oui', 'o', 'yes', 'y'], true)) {
            fwrite(STDOUT, 'Aucune modification effectuée.' . PHP_EOL);
            exit(0);
        }
    }

    $compte = strtolower(webblog_question_validee(
        'Nom du compte alwaysdata',
        null,
        '/^[a-z0-9][a-z0-9-]{1,62}$/',
        'Utilisez le nom exact du compte, sans espace ni @.'
    ));
    $adresse = strtolower(webblog_question_validee(
        'Adresse du site, sans https://',
        $compte . '.alwaysdata.net',
        '/^(?=.{1,253}$)([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/',
        'Exemple : moncompte.alwaysdata.net'
    ));
    $base = webblog_question_validee(
        'Nom de la base MySQL',
        $compte . '_webblog',
        '/^[A-Za-z0-9_-]{1,64}$/',
        'Lettres, chiffres, tirets et tirets bas uniquement.'
    );
    $utilisateur = webblog_question_validee(
        'Utilisateur MySQL',
        $compte,
        '/^[A-Za-z0-9_-]{1,64}$/',
        'Copiez le nom exact indiqué dans Databases > MySQL.'
    );
    $version = webblog_question_validee(
        'Version MariaDB choisie chez alwaysdata',
        '11.4',
        '/^(10\.11|11\.4|11\.8)$/',
        'Choix proposés : 10.11, 11.4 ou 11.8.'
    );
    $motDePasse = webblog_mot_de_passe();

    $hote = 'mysql-' . $compte . '.alwaysdata.net';
    $url = sprintf(
        // Doctrine attend une version complète majeure.mineure.correctif.
        'mysql://%s:%s@%s:3306/%s?serverVersion=mariadb-%s.0&charset=utf8mb4',
        rawurlencode($utilisateur),
        rawurlencode($motDePasse),
        $hote,
        rawurlencode($base),
        $version
    );
    $secret = bin2hex(random_bytes(32));

    $lignes = [
        '# Configuration de production Webblog sur alwaysdata.',
        '# Fichier privé : ne jamais ajouter à Git ni partager.',
        'APP_ENV=prod',
        'APP_DEBUG=0',
        'APP_SECRET=' . $secret,
        'DEFAULT_URI=https://' . $adresse,
        'DATABASE_URL="' . $url . '"',
        'MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0',
        'MAILER_DSN=null://null',
        '# Le proxy alwaysdata transmet le protocole réel via X-Forwarded-Proto.',
        'TRUSTED_PROXIES="127.0.0.1,REMOTE_ADDR"',
        '',
    ];

    umask(0077);
    if (file_put_contents($cible, implode("\n", $lignes), LOCK_EX) === false) {
        throw new RuntimeException('Écriture de .env.local impossible. Vérifiez les permissions du projet.');
    }
    if (!chmod($cible, 0600)) {
        throw new RuntimeException('Le fichier a été écrit mais ses permissions n’ont pas pu être restreintes à 0600.');
    }

    fwrite(STDOUT, PHP_EOL . '[OK] .env.local créé en mode production, permissions 0600.' . PHP_EOL);
    fwrite(STDOUT, 'Site prévu : https://' . $adresse . PHP_EOL);
    fwrite(STDOUT, 'Serveur MySQL : ' . $hote . PHP_EOL);
    fwrite(STDOUT, 'Prochaine étape : php deploy/alwaysdata/check.php' . PHP_EOL);
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, PHP_EOL . '[ERREUR] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
