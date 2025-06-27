<?php

namespace App\Service\Main;

use App\Entity\User;

interface MainServiceInterface
{
    public function getFollowingChunk(int $offset, int $limit, User $user): array;

}
