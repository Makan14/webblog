<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ArticleImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Image liée à un article : fichier, texte alternatif et ordre d'affichage.
 */
#[ORM\Entity(repositoryClass: ArticleImageRepository::class)]
#[ORM\Table(name: 'article_image')]
class ArticleImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $fichier = '';

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $alt = null;

    #[ORM\Column]
    private int $position = 0;

    #[ORM\Column]
    private bool $principale = false;

    #[ORM\Column]
    private \DateTimeImmutable $dateCreation;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Article $article = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getFichier(): string { return $this->fichier; }
    public function setFichier(string $fichier): static { $this->fichier = $fichier; return $this; }

    public function getAlt(): ?string { return $this->alt; }
    public function setAlt(?string $alt): static { $this->alt = $alt ? trim($alt) : null; return $this; }

    public function getPosition(): int { return $this->position; }
    public function setPosition(int $position): static { $this->position = $position; return $this; }

    public function isPrincipale(): bool { return $this->principale; }
    public function setPrincipale(bool $principale): static { $this->principale = $principale; return $this; }

    public function getDateCreation(): \DateTimeImmutable { return $this->dateCreation; }

    public function getArticle(): ?Article { return $this->article; }
    public function setArticle(?Article $article): static { $this->article = $article; return $this; }

    public function getCheminPublic(): string { return 'uploads/articles/' . $this->fichier; }
}
