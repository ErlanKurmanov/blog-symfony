<?php

namespace App\Controller;

use App\Form\UserForm;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile')]
final class UserController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EmailVerifier $emailVerifier,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly Security $security
    )
    {
    }

    #[Route('/', name: 'app_my_profile', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function show(): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function edit(Request $request): Response
    {
        $user = $this->getUser();
        $originalEmail = $user->getEmail();

        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if($user->getEmail() !== $originalEmail) {
                    $user->setIsVerified(false);

                    $this->entityManager->persist($user);
                    $this->entityManager->flush();

                    $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                        (new TemplatedEmail())
                            ->from(new Address('no-reply@mywebsite.com', 'Blog Mail Bot'))
                            ->to($user->getEmail())
                            ->subject('Please Confirm your New Email')
                            ->htmlTemplate('registration/confirmation_email.html.twig')
                    );

                    $this->addFlash('success', 'Email changed! Please confirm your new email address.');
                    return $this->redirectToRoute('app_logout');
                }

                $plainPassword = $form->get('plainPassword')->getData();
                if ($plainPassword) {
                    $hashedPassword = $this->userPasswordHasher->hashPassword($user, $plainPassword);
                    $user->setPassword($hashedPassword);
                }


                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $user->setImageFile(null);

                $this->addFlash('success', 'Profile updated successfully.');
                return $this->redirectToRoute('app_my_profile');

            } catch (\Exception $e) {
                error_log($e->getMessage());
                $this->addFlash('error', 'An error occurred while updating your profile. Please try again.');
            }
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/delete', name: 'app_user_delete', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(Request $request): Response
    {
        $user = $this->getUser();

        if (!$this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $this->security->logout(false);


        $this->addFlash('success', 'Your account has been deleted.');

        return $this->redirectToRoute('app_login');
    }
}
