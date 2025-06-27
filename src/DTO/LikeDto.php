<?php

namespace App\DTO;

class LikeDto
{
    private int $likes;
    private int $dislikes;

    /**
     * @param int $likes
     * @param int $dislikes
     */
    public function __construct(int $likes, int $dislikes)
    {
        $this->likes = $likes;
        $this->dislikes = $dislikes;
    }

    public function getLikes(): int
    {
        return $this->likes;
    }

    public function setLikes(int $likes): void
    {
        $this->likes = $likes;
    }

    public function getDislikes(): int
    {
        return $this->dislikes;
    }

    public function setDislikes(int $dislikes): void
    {
        $this->dislikes = $dislikes;
    }


}
