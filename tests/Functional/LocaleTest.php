<?php

declare(strict_types=1);

namespace App\Tests\Functional;

/**
 * Vérifie les quatre langues et le changement de langue.
 */
final class LocaleTest extends WebblogTestCase
{
    public function testChangementDeLangue(): void
    {
        $this->chargerArticlesDemo();
        $this->client->request('GET', '/locale/en?redirect=/');
        $this->assertResponseRedirects('/');
        $crawler = $this->client->followRedirect();
        $this->assertStringContainsString('Who is the best Brazilian player?', $this->client->getResponse()->getContent());
        $this->client->request('GET', '/locale/pt?redirect=/');
        $this->client->followRedirect();
        $this->assertStringContainsString('Quem é o melhor jogador brasileiro?', $this->client->getResponse()->getContent());
        $this->client->request('GET', '/locale/es?redirect=/');
        $this->client->followRedirect();
        $this->assertStringContainsString('¿Quién es el mejor jugador brasileño?', $this->client->getResponse()->getContent());
        $this->client->request('GET', '/locale/fr?redirect=/');
        $this->client->followRedirect();
        $this->assertStringContainsString('Qui est le meilleur joueur brésilien ?', $this->client->getResponse()->getContent());
    }
}
