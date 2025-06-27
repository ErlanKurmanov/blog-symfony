<?php

namespace App\Service\Like;

use App\DTO\LikeDto;
use App\Entity\Post;
use App\Entity\PostLike;
use App\Entity\User;
use App\Repository\PostLikeRepository;
use Doctrine\ORM\EntityManagerInterface;

class LikeService implements LikeServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PostLikeRepository $likeRepo,
    ){}

    /**
     * Toggles a user's reaction (like or dislike) on a given post.
     *
     * @param User $user
     * @param Post $post
     * @param string $type
     * @return LikeDto
     */
    public function toggleReaction(User $user, Post $post, string $type)
    {
        if (!in_array($type, ['like', 'dislike'])) {
            throw new \InvalidArgumentException('Invalid reaction type');
        }

        $existingReaction = $this->likeRepo->findOneBy(['user' => $user, 'post' => $post]);

        if ($existingReaction) {
            if ($existingReaction->getType() === $type) {
                $this->entityManager->remove($existingReaction);

                if ($type === 'like') {
                    $post->decrementLikes();
                } else {
                    $post->decrementDislikes();
                }
            } else {
                if ($existingReaction->getType() === 'like') {
                    $post->decrementLikes();
                    $post->incrementDislikes();
                } else {
                    $post->decrementDislikes();
                    $post->incrementLikes();
                }

                $existingReaction->setType($type);
                $this->entityManager->persist($existingReaction);
            }
        } else {
            $newReaction = new PostLike();
            $newReaction->setUser($user);
            $newReaction->setPost($post);
            $newReaction->setType($type);
            $this->entityManager->persist($newReaction);

            if ($type === 'like') {
                $post->incrementLikes();
            } else {
                $post->incrementDislikes();
            }
        }

        $this->entityManager->flush();

        $likes = $post->getLikesCount();
        $dislikes = $post->getDislikesCount();
        return new LikeDto($likes, $dislikes);

    }
}
