<?php

namespace App\Controller;

use App\Entity\Post;

use App\Service\Like\LikeServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LikeController extends AbstractController
{
    public function __construct(
        private readonly LikeServiceInterface $likeService,
    )
    {
    }

    #[Route('/post/{id}/react/{type}', name: 'app_post_react', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function react(Post $post, string $type): JsonResponse
    {
        try {
            $user = $this->getUser();
            $counts = $this->likeService->toggleReaction($user, $post, $type);
            dump($counts);
            return $this->json([
                'message' => 'Reaction updated',
                'likes' => $counts->getLikes(),
                'dislikes' => $counts->getDislikes(),
            ]);

        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'error' => $e->getMessage()
                ]
            );
        }
    }
}
