<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostForm;
use App\Repository\PostRepository;
use App\Service\Post\PostServiceInterface;
use App\Service\EmailService;
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
        private readonly PostServiceInterface $postService,
        private readonly EmailService $emailService
    )
    {
    }

    #[Route('', name: 'app_post_index', methods: ['GET'])]
    public function index(): Response
    {
        // Only show approved posts to regular users
        $latestPosts = $this->postRepository->findLatestPosts(5);
        return $this->render('post/index.html.twig', [
            'posts' => $latestPosts,
            'title' => 'All Posts',
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

    #[Route('/my-post', name: 'app_user_posts', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getMyPost()
    {
        $user = $this->getUser();
        $posts = $user->getPosts();
        return $this->render(
            'post/index.html.twig', [
                'posts' => $posts,
                'title' => 'My Posts',
            ]
        );
    }

    #[Route('/my-post/feed', name: 'app_my_post_feed_chunk', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getMyPostFeedChunk(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $offset = $request->query->getInt('offset', 0);
        $limit = $request->query->getInt('limit', 5);

        $feed = $this->postService->getUserPostsChunk($user, $offset, $limit);

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
            // Post is created with 'pending' status by default
            $this->entityManager->persist($post);
            $this->entityManager->flush();

            $this->emailService->sendNewPostNotificationToAdmins($post);

            $this->addFlash('success', 'Post created successfully! It is now pending admin approval.');
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
        // Only allow viewing approved posts, unless it's the author viewing their own post
        if (!$post->isApproved() && (!$this->getUser() || !$post->isAuthor($this->getUser()))) {
            throw $this->createNotFoundException('Post not found or not yet approved.');
        }

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

        // Don't allow editing approved posts
        if ($post->isApproved()) {
            $this->addFlash('error', 'You cannot edit an approved post.');
            return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
        }

        $form = $this->createForm(PostForm::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Reset status to pending when edited
            $post->setStatus('pending');
            $this->entityManager->flush();

            $this->emailService->sendNewPostNotificationToAdmins($post);

            $this->addFlash('success', 'Post updated successfully! It is now pending admin approval again.');
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
