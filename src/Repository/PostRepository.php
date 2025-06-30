<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }



    /**
     * Find posts by specific authors (existing method - make sure it exists)
     */
    public function findByAuthors(array $authors, int $limit = 5, int $offset = 0): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.author IN (:authors)')
            ->setParameter('authors', $authors)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    public function countByAuthors(array $authors): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.author IN (:authors)')
            ->setParameter('authors', $authors)
            ->getQuery()
            ->getSingleScalarResult();
    }
    /**
     * Retrieves the 5 most recent posts.
     *
     * @return Post[] Returns an array of Post objects
     */
    public function findLatestPosts(int $limit = 5, int $offset = 0): array
    {
        return $this->createQueryBuilder('p')
        ->orderBy('p.createdAt', 'DESC')
        ->setMaxResults($limit)
        ->setFirstResult(0)
        ->getQuery()
        ->getResult();
    }

    public function countAllPosts(): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

//    public function findLatestPostOfFollowing()
//    {
//        return $this->createQueryBuilder('p')
//
//
//    }
//    /**
//     * @return Post[] Returns an array of Post objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Post
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
