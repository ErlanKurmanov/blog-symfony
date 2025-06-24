<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\PostLike;
use App\Repository\PostLikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LikeController extends AbstractController
{
    #[Route('/post/{id}/react/{type}', name: 'app_post_react', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function react(Post $post, string $type, EntityManagerInterface $em, PostLikeRepository $likeRepo): JsonResponse
    {
        if (!in_array($type, ['like', 'dislike'])) {
            return $this->json(['error' => 'Invalid reaction type'], 400);
        }

        $user = $this->getUser();
        $existingReaction = $likeRepo->findOneBy(['user' => $user, 'post' => $post]);

        if ($existingReaction) {
            // Если реакция та же - удаляем ее
            if ($existingReaction->getType() === $type) {
                $em->remove($existingReaction);
            } else {
                // Если реакция другая - меняем ее
                $existingReaction->setType($type);
                $em->persist($existingReaction);
            }
        } else {
            // Если реакции не было - создаем новую
            $newReaction = new PostLike();
            $newReaction->setUser($user);
            $newReaction->setPost($post);
            $newReaction->setType($type);
            $em->persist($newReaction);
        }

        $em->flush();

        // Считаем новые количества лайков/дизлайков
        $likes = $likeRepo->count(['post' => $post, 'type' => 'like']);
        $dislikes = $likeRepo->count(['post' => $post, 'type' => 'dislike']);

        return $this->json([
            'message' => 'Reaction updated',
            'likes' => $likes,
            'dislikes' => $dislikes
        ]);
    }
}
