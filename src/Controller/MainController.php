<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(PostRepository $postRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Get followed people's posts
        $following = $user->getFollowing();

        $posts = [];
        // If the user subscribed to someone, search its posts
        if (!$following->isEmpty()) {
            $posts = $postRepository->findByAuthors($following->toArray());
        }

        return $this->render('main/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/feed', name: 'app_feed_chunk', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getFeedChunk(Request $request, PostRepository $postRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $following = $user->getFollowing();

        if ($following->isEmpty()) {
            return new Response('', 204); // No content
        }

        $page = $request->query->getInt('page', 1);
        $posts = $postRepository->findByAuthorsPaginated($following->toArray(), $page);

        if (empty($posts)) {
            return new Response('', 204); // No more posts to load
        }

        return $this->render('main/_post_chunk.html.twig', [
            'posts' => $posts,
        ]);
    }
}
