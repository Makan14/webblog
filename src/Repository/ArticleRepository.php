<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Requêtes de lecture des articles publics et de l'administration.
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /** @return list<Article> */
    public function findTriesParDate(): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.dateCreation', 'DESC')
            ->addOrderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneBySlug(string $slug): ?Article
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /** @return list<Article> */
    public function findPublicTries(): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
