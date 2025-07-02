<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Service\Main\MainServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class MainController extends AbstractController
{
    public function __construct(
        private readonly PostRepository $postRepository,
        private readonly MainServiceInterface $mainService,
    )
    {
    }

    #[Route('/', name: 'app_main')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(): Response
    {
        $user = $this->getUser();
        $following = $user->getFollowing();

        $posts = [];
        if (!$following->isEmpty()) {
            $posts = $this->postRepository->findByAuthors($following->toArray());
        }

        return $this->render('main/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/following-feed', name: 'app_following_feed_chunk', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getFollowingPostsChunk(Request $request, PostRepository $postRepository): JsonResponse
    {
        $user = $this->getUser();

        $offset = $request->query->getInt('offset', 0);
        $limit = $request->query->getInt('limit', 5);

        $data = $this->mainService->getFollowingChunk($offset, $limit, $user);

        return new JsonResponse($data);
    }
}
