<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Compte unique du blog : la première inscription devient administrateur.
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'utilisateur')]
#[UniqueEntity(fields: ['email'], message: 'registration.email_unique')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'validation.not_blank')]
    #[Assert\Email(message: 'validation.email')]
    #[Assert\Length(max: 180)]
    private string $email = '';

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'validation.not_blank')]
    #[Assert\Length(min: 2, max: 100)]
    private string $nom = '';

    /** @var list<string> */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private string $password = '';

    #[ORM\Column]
    private \DateTimeImmutable $dateCreation;

    /** @var Collection<int, Article> */
    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'auteur')]
    private Collection $articles;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->dateCreation = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): static { $this->email = mb_strtolower(trim($email)); return $this; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = trim($nom); return $this; }

    public function getUserIdentifier(): string { return $this->email; }

    /** @return list<string> */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_values(array_unique($roles));
    }

    /** @param list<string> $roles */
    public function setRoles(array $roles): static { $this->roles = $roles; return $this; }

    public function getPassword(): string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }

    public function getDateCreation(): \DateTimeImmutable { return $this->dateCreation; }

    /** @return Collection<int, Article> */
    public function getArticles(): Collection { return $this->articles; }

    public function eraseCredentials(): void
    {
        // Aucun mot de passe en clair n'est conservé sur l'entité.
    }
}
