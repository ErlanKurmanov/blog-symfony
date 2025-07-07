<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
        private UrlGeneratorInterface $urlGenerator,
        private UserRepository $userRepository,
        private string $fromEmail = 'noreply@blogapp.com'
    ) {}

    /**
     * Send notification to admins when a new post is created
     */
    public function sendNewPostNotificationToAdmins(Post $post): void
    {
        $admins = $this->userRepository->findAdmins();

        if (empty($admins)) {
            return;
        }

        $subject = 'New Post Pending Approval: ' . $post->getTitle();
        $adminUrl = $this->urlGenerator->generate('admin', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $htmlContent = $this->twig->render('emails/new_post_admin_notification.html.twig', [
            'post' => $post,
            'adminUrl' => $adminUrl,
        ]);

        foreach ($admins as $admin) {
            $email = (new Email())
                ->from($this->fromEmail)
                ->to($admin->getEmail())
                ->subject($subject)
                ->html($htmlContent);

            $this->mailer->send($email);
        }
    }

    /**
     * Send notification to author when post is approved
     */
    public function sendPostApprovedNotification(Post $post): void
    {
        if (!$post->getAuthor()) {
            return;
        }

        $subject = 'Your Post Has Been Approved: ' . $post->getTitle();
        $postUrl = $this->urlGenerator->generate('app_post_show',
            ['id' => $post->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $htmlContent = $this->twig->render('emails/post_approved.html.twig', [
            'post' => $post,
            'postUrl' => $postUrl,
        ]);

        $email = (new Email())
            ->from($this->fromEmail)
            ->to($post->getAuthor()->getEmail())
            ->subject($subject)
            ->html($htmlContent);

        $this->mailer->send($email);
    }

    /**
     * Send notification to author when post is rejected
     */
    public function sendPostRejectedNotification(Post $post, ?string $reason = null): void
    {
        if (!$post->getAuthor()) {
            return;
        }

        $subject = 'Your Post Has Been Rejected: ' . $post->getTitle();

        $htmlContent = $this->twig->render('emails/post_rejected.html.twig', [
            'post' => $post,
            'reason' => $reason,
        ]);

        $email = (new Email())
            ->from($this->fromEmail)
            ->to($post->getAuthor()->getEmail())
            ->subject($subject)
            ->html($htmlContent);

        $this->mailer->send($email);
    }
}
