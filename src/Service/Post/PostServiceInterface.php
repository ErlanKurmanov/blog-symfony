<?php

namespace App\Service\Post;

interface PostServiceInterface
{
    public function getFeedChunk(int $offset, int $limit): array;
}
