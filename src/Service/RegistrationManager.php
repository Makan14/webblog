<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Inscription du compte unique : verrou MySQL pour éviter deux administrateurs.
 */
final class RegistrationManager
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly EntityManagerInterface $em,
        private readonly Connection $connection,
        private readonly UserPasswordHasherInterface $hasher,
    ) {}

    public function compteExiste(): bool
    {
        return $this->users->count([]) > 0;
    }

    public function creerAdministrateur(string $nom, string $email, string $motDePasse): User
    {
        $verrou = 'webblog_inscription_unique';
        $ok = $this->connection->executeQuery('SELECT GET_LOCK(?, 10)', [$verrou])->fetchOne();
        if ((int) $ok !== 1) {
            throw new \RuntimeException('registration.lock');
        }
        try {
            if ($this->compteExiste()) {
                throw new \RuntimeException('registration.closed');
            }
            $user = new User();
            $user->setNom($nom)->setEmail($email)->setRoles(['ROLE_ADMIN']);
            $user->setPassword($this->hasher->hashPassword($user, $motDePasse));
            $this->em->persist($user);
            $this->em->flush();
            return $user;
        } finally {
            $this->connection->executeQuery('SELECT RELEASE_LOCK(?)', [$verrou]);
        }
    }
}
