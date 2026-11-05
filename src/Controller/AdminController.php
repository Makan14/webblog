<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Article;
use App\Entity\ArticleImage;
use App\Form\ArticleImageType;
use App\Form\ArticleType;
use App\Repository\ArticleImageRepository;
use App\Repository\ArticleRepository;
use App\Service\ImageUploader;
use App\Service\SlugService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Administration : CRUD des articles, des textes et des images.
 */
#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ArticleRepository $articles,
        private readonly ArticleImageRepository $images,
        private readonly ImageUploader $uploader,
        private readonly SlugService $slugs,
    ) {}

    #[Route('', name: 'app_admin_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'articles' => $this->articles->findTriesParDate(),
            'total_images' => $this->images->count([]),
        ]);
    }

    #[Route('/articles', name: 'app_admin_article_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/article_index.html.twig', [
            'articles' => $this->articles->findTriesParDate(),
        ]);
    }

    #[Route('/articles/nouveau', name: 'app_admin_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $article->setSlug($this->slugs->generer($article->getNom()));
            $article->setAuteur($this->getUser());
            $this->em->persist($article);
            $this->ajouterImages($article, $form->get('images')->getData() ?? []);
            $this->em->flush();
            $this->addFlash('success', 'admin.article_created');
            return $this->redirectToRoute('app_admin_article_edit', ['id' => $article->getId()]);
        }
        return $this->render('admin/article_form.html.twig', ['form' => $form, 'article' => null]);
    }

    #[Route('/articles/{id}/modifier', name: 'app_admin_article_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Article $article, Request $request): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $article->setSlug($this->slugs->generer($article->getNom(), $article->getId()));
            $this->ajouterImages($article, $form->get('images')->getData() ?? []);
            $this->em->flush();
            $this->addFlash('success', 'admin.article_updated');
            return $this->redirectToRoute('app_admin_article_edit', ['id' => $article->getId()]);
        }
        $formImages = [];
        foreach ($article->getImages() as $image) {
            $formImages[$image->getId()] = $this->createForm(ArticleImageType::class, $image)->createView();
        }
        return $this->render('admin/article_form.html.twig', [
            'form' => $form,
            'article' => $article,
            'image_forms' => $formImages,
        ]);
    }

    #[Route('/articles/{id}/supprimer', name: 'app_admin_article_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Article $article, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('delete-article-' . $article->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'admin.csrf_invalid');
            return $this->redirectToRoute('app_admin_article_index');
        }
        foreach ($article->getImages() as $image) {
            $this->uploader->supprimer($image->getFichier());
        }
        $this->em->remove($article);
        $this->em->flush();
        $this->addFlash('success', 'admin.article_deleted');
        return $this->redirectToRoute('app_admin_article_index');
    }

    #[Route('/images/{id}/modifier', name: 'app_admin_image_edit', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function editImage(ArticleImage $image, Request $request): Response
    {
        $article = $image->getArticle();
        $form = $this->createForm(ArticleImageType::class, $image);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($image->isPrincipale()) {
                foreach ($article->getImages() as $autre) {
                    if ($autre !== $image) {
                        $autre->setPrincipale(false);
                    }
                }
            }
            $this->em->flush();
            $this->addFlash('success', 'admin.image_updated');
        }
        return $this->redirectToRoute('app_admin_article_edit', ['id' => $article->getId()]);
    }

    #[Route('/images/{id}/supprimer', name: 'app_admin_image_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function deleteImage(ArticleImage $image, Request $request): Response
    {
        $article = $image->getArticle();
        if (!$this->isCsrfTokenValid('delete-image-' . $image->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'admin.csrf_invalid');
            return $this->redirectToRoute('app_admin_article_edit', ['id' => $article->getId()]);
        }
        $etaitPrincipale = $image->isPrincipale();
        $this->uploader->supprimer($image->getFichier());
        $article->removeImage($image);
        $this->em->remove($image);
        if ($etaitPrincipale) {
            $premiere = $article->getImages()->first();
            if ($premiere) {
                $premiere->setPrincipale(true);
            }
        }
        $this->em->flush();
        $this->addFlash('success', 'admin.image_deleted');
        return $this->redirectToRoute('app_admin_article_edit', ['id' => $article->getId()]);
    }

    /** @param iterable<int, \Symfony\Component\HttpFoundation\File\UploadedFile> $fichiers */
    private function ajouterImages(Article $article, iterable $fichiers): void
    {
        $position = $article->getImages()->count();
        foreach ($fichiers as $fichier) {
            if (!$fichier) {
                continue;
            }
            try {
                $nom = $this->uploader->televerser($fichier);
            } catch (\RuntimeException $e) {
                $this->addFlash('danger', $e->getMessage());
                continue;
            }
            $image = new ArticleImage();
            $image->setFichier($nom)
                ->setAlt($article->getJoueurLibelle())
                ->setPosition($position)
                ->setPrincipale(0 === $position);
            $article->addImage($image);
            $this->em->persist($image);
            $position++;
        }
    }
}
