<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ArticleRepository;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Génère un slug unique et lisible pour chaque article.
 */
final class SlugService
{
    public function __construct(
        private readonly SluggerInterface $slugger,
        private readonly ArticleRepository $articles,
    ) {}

    public function generer(string $titre, ?int $ignorerId = null): string
    {
        $base = mb_strtolower($this->slugger->slug($titre)->toString());
        $base = trim($base, '-') ?: 'article';
        $slug = $base;
        $i = 2;
        while (true) {
            $existant = $this->articles->findOneBySlug($slug);
            if (!$existant || $existant->getId() === $ignorerId) {
                return $slug;
            }
            $slug = $base . '-' . $i;
            $i++;
        }
    }
}
