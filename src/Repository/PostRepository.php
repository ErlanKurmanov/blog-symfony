<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
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
            ->andWhere('p.status = :status')
            ->setParameter('authors', $authors)
            ->setParameter('status', 'approved')
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
     * Find latest approved posts (only approved posts are visible to users)
     */
    public function findLatestPosts(int $limit = 5): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->setParameter('status', 'approved')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countAllPosts(): int
    {
        return $this->createQueryBuilder('p')
            ->where('p.status = :status')
            ->setParameter('status', 'approved')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findMyPosts(User $user,int $limit = 5, int $offset = 0): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.author = :user')
            ->setParameter('user', $user)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find approved posts for feed with pagination
     */
    public function findApprovedPosts(int $offset = 0, int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->setParameter('status', 'approved')
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find user's posts with pagination (user can see their own posts regardless of status)
     */
    public function findUserPosts(User $user, int $offset = 0, int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.author = :user')
            ->setParameter('user', $user)
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find posts by followed users (only approved posts)
     */
    public function findFollowingPosts(User $user, int $offset = 0, int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.author', 'a')
            ->join('a.followers', 'f')
            ->andWhere('f = :user')
            ->andWhere('p.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'approved')
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count posts by status
     */
    public function countByStatus(string $status): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find pending posts for admin review
     */
    public function findPendingPosts(int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->setParameter('status', 'pending')
            ->orderBy('p.createdAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
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
