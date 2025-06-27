<?php

namespace App\Service\Main;

use App\Entity\User;
use App\Repository\PostRepository;

class MainService implements MainServiceInterface
{
    public function __construct(
        private readonly PostRepository $postRepository,
    )
    {
    }

    /**
     * Retrieves a chunk of posts from users that a given user is following,
     *  along with pagination information.
     * @param int $offset
     * @param int $limit
     * @param User $user
     * @return array
     */
    public function getFollowingChunk(int $offset, int $limit, User $user): array
    {

        $posts = [];
        $totalPostsCount = 0;
        $following = $user->getFollowing();
        if (!$following->isEmpty()) {
            $followingArray = $following->toArray();
            $posts = $this->postRepository->findByAuthors($followingArray, $limit, $offset);
            $totalPostsCount = $this->postRepository->countByAuthors($followingArray);
        }

        $hasMore = ($offset + count($posts)) < $totalPostsCount;

        $data = array_map(function($post) use ($user) {
            return [
                'id' => $post->getId(),
                'image' => $post->getImage(),
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
                'createdAt' => $post->getCreatedAt()->format('c'),
                'author' => [
                    'email' => $post->getAuthor()->getEmail(),
                ],
                'likesCount' => $post->getLikesCount(),
                'dislikesCount' => $post->getDislikesCount(),
                'isLikedByUser' => $post->isLikedByUser($user),
                'isDislikedByUser' => $post->isDislikedByUser($user),
            ];
        }, $posts);

        return [
            'posts' => $data,
            'hasMore' => $hasMore,
        ];
    }
}
