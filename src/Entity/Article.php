<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use SortDirection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Article du blog : un joueur, un texte et plusieurs images.
 */
#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\Table(name: 'article')]
#[ORM\HasLifecycleCallbacks]
class Article
{
    public const JOUEURS = ['ronaldinho' => 'Ronaldinho', 'neymar' => 'Neymar', 'robinho' => 'Robinho'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'validation.not_blank')]
    #[Assert\Length(min: 3, max: 180)]
    private string $nom = '';

    #[ORM\Column(length: 200, unique: true)]
    private string $slug = '';

    #[ORM\Column(length: 30)]
    #[Assert\Choice(choices: ['ronaldinho', 'neymar', 'robinho'], message: 'validation.choice')]
    private string $joueur = 'ronaldinho';

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'validation.not_blank')]
    #[Assert\Length(max: 500)]
    private string $extrait = '';

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'validation.not_blank')]
    private string $contenu = '';

    #[ORM\Column]
    private \DateTimeImmutable $dateCreation;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateModification = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $auteur = null;

    /** @var Collection<int, ArticleImage> */
    #[ORM\OneToMany(targetEntity: ArticleImage::class, mappedBy: 'article', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => SortDirection::Ascending, 'id' => SortDirection::Ascending])]
    private Collection $images;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->dateCreation = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = trim($nom); return $this; }

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }

    public function getJoueur(): string { return $this->joueur; }
    public function setJoueur(string $joueur): static { $this->joueur = $joueur; return $this; }

    public function getJoueurLibelle(): string { return self::JOUEURS[$this->joueur] ?? $this->joueur; }

    public function getExtrait(): string { return $this->extrait; }
    public function setExtrait(string $extrait): static { $this->extrait = trim($extrait); return $this; }

    public function getContenu(): string { return $this->contenu; }
    public function setContenu(string $contenu): static { $this->contenu = $contenu; return $this; }

    public function getDateCreation(): \DateTimeImmutable { return $this->dateCreation; }
    public function getDateModification(): ?\DateTimeImmutable { return $this->dateModification; }

    #[ORM\PreUpdate]
    public function majDateModification(): void { $this->dateModification = new \DateTimeImmutable(); }

    public function getAuteur(): ?User { return $this->auteur; }
    public function setAuteur(?User $auteur): static { $this->auteur = $auteur; return $this; }

    /** @return Collection<int, ArticleImage> */
    public function getImages(): Collection { return $this->images; }

    public function addImage(ArticleImage $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setArticle($this);
        }
        return $this;
    }

    public function removeImage(ArticleImage $image): static
    {
        $this->images->removeElement($image);
        return $this;
    }

    public function getImagePrincipale(): ?ArticleImage
    {
        foreach ($this->images as $image) {
            if ($image->isPrincipale()) {
                return $image;
            }
        }
        return $this->images->first() ?: null;
    }

    public function getTempsLecture(): int
    {
        $mots = str_word_count(strip_tags($this->contenu));
        return max(1, (int) ceil($mots / 200));
    }
}
