<?php

declare(strict_types=1);

namespace App\Tests\Functional;

/**
 * Vérifie les pages publiques, les trois cadres et la navigation dans le même onglet.
 */
final class PublicBlogTest extends WebblogTestCase
{
    public function testAccueilAfficheTroisArticles(): void
    {
        $this->chargerArticlesDemo();
        $crawler = $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertCount(3, $crawler->filter('.article-card'));
        $this->assertStringContainsString('Webblog Copyright 2026', $this->client->getResponse()->getContent());
        foreach ($crawler->filter('.article-card') as $card) {
            $this->assertSame('a', $card->tagName);
            $this->assertFalse($card->hasAttribute('target'));
        }
    }

    public function testArticleCompletAfficheTexteEtGalerie(): void
    {
        $this->chargerArticlesDemo();
        $crawler = $this->client->request('GET', '/article/neymar-l-audace-creative');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Neymar', $crawler->filter('h1')->text());
        $this->assertStringContainsString('Lorem ipsum', $this->client->getResponse()->getContent());
        $this->assertGreaterThanOrEqual(2, $crawler->filter('.gallery-grid img')->count());
    }

    public function testArticleInconnuRenvoie404(): void
    {
        $this->client->request('GET', '/article/inexistant');
        $this->assertResponseStatusCodeSame(404);
    }
}
