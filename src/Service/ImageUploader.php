<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Téléversement sécurisé des images d'articles : type, taille et nom de fichier.
 */
final class ImageUploader
{
    private const TYPES_AUTORISES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function __construct(
        private readonly string $uploadDir,
        private readonly int $maxUploadSize,
        private readonly SluggerInterface $slugger,
    ) {}

    public function televerser(UploadedFile $fichier): string
    {
        if (!$fichier->isValid()) {
            throw new \RuntimeException('upload.invalid');
        }
        if ($fichier->getSize() > $this->maxUploadSize) {
            throw new \RuntimeException('upload.too_large');
        }
        $mime = $fichier->getMimeType();
        if (!isset(self::TYPES_AUTORISES[$mime])) {
            throw new \RuntimeException('upload.type');
        }
        if (!is_dir($this->uploadDir) && !mkdir($this->uploadDir, 0775, true) && !is_dir($this->uploadDir)) {
            throw new \RuntimeException('upload.directory');
        }
        $nom = Uuid::v4()->toRfc4122() . '.' . self::TYPES_AUTORISES[$mime];
        $fichier->move($this->uploadDir, $nom);
        return $nom;
    }

    public function supprimer(string $nom): void
    {
        // Empêche toute traversée de répertoire.
        $nom = basename($nom);
        $chemin = $this->uploadDir . DIRECTORY_SEPARATOR . $nom;
        if (is_file($chemin)) {
            unlink($chemin);
        }
    }

    public function getUploadDir(): string
    {
        return $this->uploadDir;
    }
}
