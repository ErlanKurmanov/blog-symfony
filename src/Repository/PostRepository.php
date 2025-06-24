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
     * Finds posts by a collection of authors, ordered by creation date.
     * @param array $authors
     * @return Post[]
     */
    public function findByAuthors(array $authors): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.author IN (:authors)')
            ->setParameter('authors', $authors)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds posts by authors with pagination
     * @param array $authors
     * @param int $page
     * @param int $limit
     * @return Post[]
     */
    public function findByAuthorsPaginated(array $authors, int $page = 1, int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.author IN (:authors)')
            ->setParameter('authors', $authors)
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all posts ordered by creation date (for all feed)
     * @param int $page
     * @param int $limit
     * @return Post[]
     */
    public function findAllPaginated(int $page = 1, int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find posts by specific user
     * @param mixed $user
     * @return Post[]
     */
    public function findByUser($user): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.author = :user')
            ->setParameter('user', $user)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

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
