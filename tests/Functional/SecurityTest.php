<?php

declare(strict_types=1);

namespace App\Tests\Functional;

/**
 * Vérifie l'inscription unique, la connexion, la déconnexion et la suppression du compte.
 */
final class SecurityTest extends WebblogTestCase
{
    public function testAdminEstProtegeSansConnexion(): void
    {
        $this->client->request('GET', '/admin');
        $this->assertResponseRedirects('/connexion');
    }

    public function testPremiereInscriptionCreeAdminPuisSeFerme(): void
    {
        $crawler = $this->client->request('GET', '/inscription');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('Créer le compte')->form([
            'registration[nom]' => 'Makan',
            'registration[email]' => 'admin@webblog.test',
            'registration[plainPassword][first]' => 'MotDePasseSolide123',
            'registration[plainPassword][second]' => 'MotDePasseSolide123',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/connexion');
        $this->assertSame(1, $this->em->getRepository(\App\Entity\User::class)->count([]));

        $this->client->request('GET', '/inscription');
        $this->assertResponseRedirects('/connexion');
    }

    public function testConnexionDeconnexionEtSuppressionCompte(): void
    {
        $this->creerAdmin();
        $crawler = $this->client->request('GET', '/connexion');
        $form = $crawler->selectButton('Se connecter')->form([
            'email' => 'admin@webblog.test',
            'password' => 'MotDePasseSolide123',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/admin');
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $crawler = $this->client->request('GET', '/compte');
        $form = $crawler->selectButton('Supprimer définitivement')->form([
            'account_delete[password]' => 'MotDePasseSolide123',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/');
        $this->assertSame(0, $this->em->getRepository(\App\Entity\User::class)->count([]));
    }

    public function testMauvaisMotDePasseRefuseLaSuppression(): void
    {
        $this->connecterAdmin();
        $crawler = $this->client->request('GET', '/compte');
        $form = $crawler->selectButton('Supprimer définitivement')->form([
            'account_delete[password]' => 'incorrect',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/compte');
        $this->assertSame(1, $this->em->getRepository(\App\Entity\User::class)->count([]));
    }
}
