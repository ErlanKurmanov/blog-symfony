<?php

namespace App\Service\Post;

use Symfony\Component\Security\Core\User\UserInterface;

interface PostServiceInterface
{
    public function getFeedChunk(int $offset, int $limit): array;

    public function getUserPostsChunk(UserInterface $user, int $offset, int $limit);
}
