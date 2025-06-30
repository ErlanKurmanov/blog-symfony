<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostForm;
use App\Repository\PostRepository;
use App\Service\Post\PostServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/post')]
final class PostController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PostRepository $postRepository,
        private readonly PostServiceInterface $postService
    )
    {
    }

    #[Route('', name: 'app_post_index', methods: ['GET'])]
    public function index(): Response
    {
        $latestPosts = $this->postRepository->findLatestPosts(5);
        return $this->render('post/index.html.twig', [
            'posts' => $latestPosts,
        ]);
    }

    #[Route('/feed', name: 'app_post_feed_chunk', methods: ['GET'])]
    public function getPostFeedChunk(Request $request): JsonResponse
    {
        $offset = $request->query->getInt('offset', 0);
        $limit = $request->query->getInt('limit', 5);
        $feed = $this->postService->getFeedChunk($offset, $limit);

        return new JsonResponse($feed);
    }

    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function new(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(PostForm::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setAuthor($this->getUser());
            $this->entityManager->persist($post);
            $this->entityManager->flush();

            $this->addFlash('success', 'Post created successfully!');
            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function edit(Request $request, Post $post): Response
    {
        if (!$post->isAuthor($this->getUser())) {
            $this->addFlash('error', 'You can only edit your own posts.');
            return $this->redirectToRoute('app_post_index');
        }

        $form = $this->createForm(PostForm::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Post updated successfully!');
            return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_delete', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(Request $request, Post $post): Response
    {
        if (!$post->isAuthor($this->getUser())) {
            $this->addFlash('error', 'You can only delete your own posts.');
            return $this->redirectToRoute('app_post_index');
        }

        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->getPayload()->getString('_token'))) {
            $this->entityManager->remove($post);
            $this->entityManager->flush();
            $this->addFlash('success', 'Post deleted successfully!');
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }
}
