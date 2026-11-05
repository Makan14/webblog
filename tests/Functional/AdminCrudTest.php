<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Article;
use App\Entity\ArticleImage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Vérifie le CRUD des articles, des textes et des images côté administration.
 */
final class AdminCrudTest extends WebblogTestCase
{
    private function imageTest(): UploadedFile
    {
        $chemin = sys_get_temp_dir() . '/webblog-test-' . uniqid() . '.png';
        $im = imagecreatetruecolor(80, 60);
        imagefill($im, 0, 0, imagecolorallocate($im, 109, 92, 231));
        imagepng($im, $chemin);
        imagedestroy($im);
        return new UploadedFile($chemin, 'test.png', 'image/png', null, true);
    }

    public function testCreerModifierSupprimerArticleEtImages(): void
    {
        $this->connecterAdmin();
        $crawler = $this->client->request('GET', '/admin/articles/nouveau');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('Créer l’article')->form([
            'article[nom]' => 'Article de test CRUD',
            'article[joueur]' => 'neymar',
            'article[extrait]' => 'Lorem ipsum dolor sit amet.',
            'article[contenu]' => "Lorem ipsum dolor sit amet.\n\n## Titre\n\nConsectetur adipiscing elit.",
            'article[images]' => [$this->imageTest()],
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $article = $this->em->getRepository(Article::class)->findOneBy(['nom' => 'Article de test CRUD']);
        $this->assertNotNull($article);
        $this->assertCount(1, $article->getImages());
        $this->assertTrue($article->getImages()->first()->isPrincipale());

        $crawler = $this->client->request('GET', '/admin/articles/' . $article->getId() . '/modifier');
        $form = $crawler->selectButton('Enregistrer')->form([
            'article[nom]' => 'Article de test CRUD modifié',
            'article[extrait]' => 'Extrait modifié.',
            'article[contenu]' => 'Contenu modifié.',
        ]);
        $this->client->submit($form);
        $this->em->clear();
        $article = $this->em->getRepository(Article::class)->find($article->getId());
        $this->assertSame('Article de test CRUD modifié', $article->getNom());
        $this->assertSame('Extrait modifié.', $article->getExtrait());

        $image = $article->getImages()->first();
        $fichier = $image->getFichier();
        $crawler = $this->client->request('GET', '/admin/articles/' . $article->getId() . '/modifier');
        $deleteForm = $crawler->filter('form[action*="/admin/images/"][action*="/supprimer"]')->first()->form();
        $this->client->submit($deleteForm);
        $this->em->clear();
        $this->assertNull($this->em->getRepository(ArticleImage::class)->find($image->getId()));
        $this->assertFileDoesNotExist(static::getContainer()->getParameter('app.upload_dir') . '/' . $fichier);

        $crawler = $this->client->request('GET', '/admin/articles');
        $deleteArticleForm = $crawler->filter('form[action*="/admin/articles/"]')->first()->form();
        $this->client->submit($deleteArticleForm);
        $this->em->clear();
        $this->assertNull($this->em->getRepository(Article::class)->find($article->getId()));
    }

    public function testModificationImageAltEtPrincipale(): void
    {
        $this->connecterAdmin();
        $this->chargerArticlesDemo();
        $article = $this->em->getRepository(Article::class)->findOneBy(['joueur' => 'ronaldinho']);
        $image = $article->getImages()->last();
        $crawler = $this->client->request('GET', '/admin/articles/' . $article->getId() . '/modifier');
        $forms = $crawler->filter('form[action*="/admin/images/"][method="post"]');
        $target = null;
        foreach ($forms as $f) {
            if (str_contains($f->getAttribute('action'), '/admin/images/' . $image->getId() . '/modifier')) {
                $target = $f;
                break;
            }
        }
        $this->assertNotNull($target);
        $form = new \Symfony\Component\DomCrawler\Form($target, $this->client->getHistory()->current()->getUri());
        $form['article_image[alt]'] = 'Nouveau texte alternatif';
        $form['article_image[principale]']->tick();
        $this->client->submit($form);
        $this->em->clear();
        $image = $this->em->getRepository(ArticleImage::class)->find($image->getId());
        $this->assertSame('Nouveau texte alternatif', $image->getAlt());
        $this->assertTrue($image->isPrincipale());
        $article = $this->em->getRepository(Article::class)->find($article->getId());
        $this->assertSame($image->getId(), $article->getImagePrincipale()->getId());
    }
}
