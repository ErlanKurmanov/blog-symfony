<?php

namespace App\Service\Post;

use App\Repository\PostRepository;
use Symfony\Component\Security\Core\User\UserInterface;

class PostService implements PostServiceInterface
{
    public function __construct(
        private readonly PostRepository $postRepository,
    )
    {
    }


    /**
     * Retrieves a paginated chunk of the latest posts for the general feed.
     *
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getFeedChunk(int $offset, int $limit): array
    {

        $posts = $this->postRepository->findLatestPosts($limit, $offset);

        $totalPostsCount = $this->postRepository->countAllPosts();
        $hasMore = ($offset + count($posts)) < $totalPostsCount;

        $data = array_map(function($post) {
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
            ];
        }, $posts);


        return [
            'posts' => $data,
            'hasMore' => $hasMore,
        ];
    }


    /**
     * Retrieves a paginated chunk of the user's posts for the my-post feed
     *
     * @param UserInterface $user
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getUserPostsChunk(UserInterface $user, int $offset, int $limit)
    {
        $posts = $this->postRepository->findMyPosts($user, $limit, $offset);

        $totalPostsCount = $this->postRepository->countAllPosts();
        $hasMore = ($offset + count($posts)) < $totalPostsCount;
        $data = array_map(function($post) {
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
            ];
        }, $posts);

        return [
            'posts' => $data,
            'hasMore' => $hasMore,
        ];
    }
}
