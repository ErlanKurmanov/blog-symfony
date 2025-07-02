<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class FollowController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    #[Route('/follow/{id}', name: 'app_user_follow', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function toggleFollow(User $targetUser): JsonResponse
    {
        $currentUser = $this->getUser();

        if ($targetUser->getId() === $currentUser->getId()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'You cannot follow yourself'
            ], 400);
        }

        $isFollowing = $currentUser->getFollowing()->contains($targetUser);

        if ($isFollowing) {
            $currentUser->removeFollowing($targetUser);
            $follow = false;

        } else {
            $currentUser->addFollowing($targetUser);
            $follow = true;
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'follow' => $follow,
            'isFollowing' => !$isFollowing,
            'followersCount' => $targetUser->getFollowers()->count(),
            'message' => ucfirst($follow) . ' successfully!'
        ]);

    }

}
