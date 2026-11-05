<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Base des tests fonctionnels : base MySQL de test réinitialisée avant chaque test.
 */
abstract class WebblogTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $conn = $this->em->getConnection();
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        foreach (['article_image', 'article', 'utilisateur'] as $table) {
            $conn->executeStatement('TRUNCATE TABLE ' . $table);
        }
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    protected function creerAdmin(string $email = 'admin@webblog.test', string $password = 'MotDePasseSolide123'): User
    {
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user = new User();
        $user->setNom('Administrateur')->setEmail($email)->setRoles(['ROLE_ADMIN']);
        $user->setPassword($hasher->hashPassword($user, $password));
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    protected function connecterAdmin(string $email = 'admin@webblog.test', string $password = 'MotDePasseSolide123'): User
    {
        $user = $this->creerAdmin($email, $password);
        $this->client->loginUser($user);
        return $user;
    }

    protected function chargerArticlesDemo(): void
    {
        $fixtures = static::getContainer()->get(\App\DataFixtures\AppFixtures::class);
        $fixtures->load($this->em);
    }
}
