<?php

namespace App\Service\Like;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

interface LikeServiceInterface
{
    public function toggleReaction(UserInterface $user, Post $post, string $type);
}
