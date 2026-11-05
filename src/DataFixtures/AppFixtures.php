<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\ArticleImage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Données de démonstration : trois articles, neuf photos, textes Lorem ipsum.
 * Aucun compte n'est créé : la première inscription devient administrateur.
 */
final class AppFixtures extends Fixture
{
    public function __construct(
        private readonly string $resourceDir,
        private readonly string $uploadDir,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $fs = new Filesystem();
        $fs->mkdir($this->uploadDir);

        $lorem = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\n\n## Lorem ipsum dolor\n\nDuis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.\n\nSed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.\n\n## Nemo enim ipsam\n\nNemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit.";

        $data = [
            ['nom' => 'Ronaldinho : la magie du jeu', 'slug' => 'ronaldinho-la-magie-du-jeu', 'joueur' => 'ronaldinho', 'images' => ['ronaldinho-1.jpg', 'ronaldinho-2.jpg', 'ronaldinho-3.jpg'], 'extrait' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'],
            ['nom' => 'Neymar : l’audace créative', 'slug' => 'neymar-l-audace-creative', 'joueur' => 'neymar', 'images' => ['neymar-1.jpg', 'neymar-2.jpg', 'neymar-3.jpg'], 'extrait' => 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.'],
            ['nom' => 'Robinho : vitesse et technique', 'slug' => 'robinho-vitesse-et-technique', 'joueur' => 'robinho', 'images' => ['robinho-1.jpg', 'robinho-2.jpg', 'robinho-3.jpg'], 'extrait' => 'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.'],
        ];

        foreach ($data as $i => $row) {
            $article = new Article();
            $article->setNom($row['nom'])->setSlug($row['slug'])->setJoueur($row['joueur'])
                ->setExtrait($row['extrait'])->setContenu($lorem);
            $manager->persist($article);
            foreach ($row['images'] as $j => $nom) {
                $source = $this->resourceDir . '/' . $nom;
                $cible = $this->uploadDir . '/' . $nom;
                if (is_file($source)) {
                    $fs->copy($source, $cible, true);
                }
                $image = new ArticleImage();
                $image->setFichier($nom)->setAlt($article->getJoueurLibelle())->setPosition($j)->setPrincipale(0 === $j);
                $article->addImage($image);
                $manager->persist($image);
            }
        }
        $manager->flush();
    }
}
