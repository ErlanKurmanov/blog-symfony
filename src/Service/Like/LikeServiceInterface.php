<?php

namespace App\Service\Like;

use App\Entity\Post;
use App\Entity\User;

interface LikeServiceInterface
{
    public function toggleReaction(User $user, Post $post, string $type);
}
