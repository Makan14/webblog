<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Pages publiques : liste des articles et article complet dans le même onglet.
 */
final class BlogController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(ArticleRepository $articles): Response
    {
        return $this->render('blog/index.html.twig', [
            'articles' => $articles->findPublicTries(),
        ]);
    }

    #[Route('/article/{slug}', name: 'app_article_show', methods: ['GET'])]
    public function show(string $slug, ArticleRepository $articles): Response
    {
        $article = $articles->findOneBySlug($slug);
        if (!$article) {
            throw $this->createNotFoundException('article.not_found');
        }
        return $this->render('blog/show.html.twig', ['article' => $article]);
    }
}
