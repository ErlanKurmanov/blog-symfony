<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly PostRepository $postRepository,
        private readonly AdminUrlGenerator $adminUrlGenerator
    ) {}

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $pendingPosts = $this->postRepository->count(['status' => 'pending']);
        $approvedPosts = $this->postRepository->count(['status' => 'approved']);
        $rejectedPosts = $this->postRepository->count(['status' => 'rejected']);
        $totalPosts = $this->postRepository->count([]);

        $recentPendingPosts = $this->postRepository->findBy(
            ['status' => 'pending'],
            ['createdAt' => 'DESC'],
            5
        );

        return $this->render('admin/dashboard.html.twig', [
            'pendingPosts' => $pendingPosts,
            'approvedPosts' => $approvedPosts,
            'rejectedPosts' => $rejectedPosts,
            'totalPosts' => $totalPosts,
            'recentPendingPosts' => $recentPendingPosts,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Blog Admin Panel')
            ->setFaviconPath('favicon.ico');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Content Management');
        yield MenuItem::linkToCrud('All Posts', 'fas fa-newspaper', Post::class);

        yield MenuItem::linkToUrl('Pending Posts', 'fas fa-clock',
            $this->adminUrlGenerator
                ->setController(PostCrudController::class)
                ->setAction('index')
                ->set('filters[status][comparison]', '=') // <-- ADD THIS LINE
                ->set('filters[status][value]', 'pending')
                ->generateUrl()
        )->setBadge($this->postRepository->count(['status' => 'pending']), 'warning');

        yield MenuItem::linkToUrl('Approved Posts', 'fas fa-check-circle',
            $this->adminUrlGenerator
                ->setController(PostCrudController::class)
                ->setAction('index')
                ->set('filters[status][comparison]', '=')
                ->set('filters[status][value]', 'approved')
                ->generateUrl()
        );

        yield MenuItem::linkToUrl('Rejected Posts', 'fas fa-times-circle',
            $this->adminUrlGenerator
                ->setController(PostCrudController::class)
                ->setAction('index')
                ->set('filters[status][comparison]', '=')
                ->set('filters[status][value]', 'rejected')
                ->generateUrl()
        );

        yield MenuItem::section('User Management');
        yield MenuItem::linkToCrud('Users', 'fas fa-users', User::class);

        yield MenuItem::section('Site');
        yield MenuItem::linkToUrl('Visit Site', 'fas fa-external-link-alt', '/');
        yield MenuItem::linkToLogout('Logout', 'fas fa-sign-out-alt');
    }
}
