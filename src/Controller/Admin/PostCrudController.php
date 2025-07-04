<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Entity\User;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class PostCrudController extends AbstractCrudController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AdminUrlGenerator $adminUrlGenerator,
        private EmailService $emailService,
        private RequestStack $requestStack
    ) {}

    public static function getEntityFqcn(): string
    {
        return Post::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Post')
            ->setEntityLabelInPlural('Posts')
            ->setSearchFields(['title', 'content', 'author.email'])
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->showEntityActionsInlined()
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            TextField::new('title')
                ->setColumns(6),

            TextareaField::new('content')
                ->setColumns(12)
                ->hideOnIndex()
                ->setMaxLength(500)
                ->renderAsHtml(),

            AssociationField::new('author')
                ->setColumns(6)
                ->setFormTypeOption('choice_label', 'email')
                ->hideOnForm()
                ->formatValue(function ($value, $entity) {
                    return $value ? $value->getEmail() : 'N/A';
                }),

            ChoiceField::new('status')
                ->setColumns(6)
                ->setChoices([
                    'Pending' => 'pending',
                    'Approved' => 'approved',
                    'Rejected' => 'rejected',
                ])
                ->renderAsBadges([
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                ])
                ->hideOnForm(),

            AssociationField::new('approvedBy')
                ->setColumns(6)
                ->hideOnForm()
                ->hideOnIndex()
                ->setFormTypeOption('choice_label', 'email'),

            DateTimeField::new('createdAt')
                ->setColumns(6)
                ->hideOnForm()
                ->setFormat('dd/MM/yyyy HH:mm'),

            DateTimeField::new('approvedAt')
                ->setColumns(6)
                ->hideOnForm()
                ->hideOnIndex()
                ->setFormat('dd/MM/yyyy HH:mm'),

            TextareaField::new('rejectionReason')
                ->setColumns(12)
                ->hideOnForm()
                ->hideOnIndex(),

            ImageField::new('image')
                ->setBasePath('/images/posts/')
                ->setUploadDir('public/images/posts')
                ->hideOnForm()
                ->setColumns(3),

            IntegerField::new('likesCount')
                ->hideOnForm()
                ->setColumns(3),

            IntegerField::new('dislikesCount')
                ->hideOnForm()
                ->setColumns(3),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $approveAction = Action::new('approve', 'Approve', 'fas fa-check')
            ->linkToCrudAction('approvePost')
            ->setCssClass('btn btn-success')
            ->displayIf(static function (Post $post) {
                return $post->getStatus() === 'pending';
            });

        $rejectAction = Action::new('reject', 'Reject', 'fas fa-times')
            ->linkToCrudAction('rejectPost')
            ->setCssClass('btn btn-danger')
            ->displayIf(static function (Post $post) {
                return $post->getStatus() === 'pending';
            });

        $viewAction = Action::new('view', 'View', 'fas fa-eye')
            ->linkToUrl(function (Post $post) {
                return '/post/' . $post->getId();
            })
            ->setHtmlAttributes(['target' => '_blank'])
            ->setCssClass('btn btn-info');

        return $actions
            ->add(Crud::PAGE_INDEX, $approveAction)
            ->add(Crud::PAGE_INDEX, $rejectAction)
            ->add(Crud::PAGE_INDEX, $viewAction)
            ->add(Crud::PAGE_DETAIL, $approveAction)
            ->add(Crud::PAGE_DETAIL, $rejectAction)
            ->add(Crud::PAGE_DETAIL, $viewAction)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->displayIf(static function (Post $post) {
                    return $post->getStatus() === 'rejected';
                });
            });
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('status')
            ->add('author')
            ->add('createdAt');
    }

    public function approvePost(AdminContext $context): Response
    {
        /** @var Post $post */
        $post = $context->getEntity()->getInstance();
        /** @var User $admin */
        $admin = $this->getUser();

        $post->approve($admin);
        $this->entityManager->flush();

        $this->emailService->sendPostApprovedNotification($post);

        $this->addFlash('success', sprintf('Post "%s" has been approved successfully!', $post->getTitle()));

        return $this->redirect($this->adminUrlGenerator
            ->setController(PostCrudController::class)
            ->setAction(Action::INDEX)
            ->generateUrl());
    }

    public function rejectPost(AdminContext $context): Response
    {
        /** @var Post $post */
        $post = $context->getEntity()->getInstance();
        /** @var User $admin */
        $admin = $this->getUser();

        $request = $this->requestStack->getCurrentRequest();
        $reason = $request->query->get('reason');

        if (!$reason) {
            // Render a form to get rejection reason
            return $this->render('admin/post_reject_form.html.twig', [
                'post' => $post,
                'admin_context' => $context,
            ]);
        }

        $post->reject($admin, $reason);
        $this->entityManager->flush();

        $this->emailService->sendPostRejectedNotification($post, $reason);

        $this->addFlash('success', sprintf('Post "%s" has been rejected.', $post->getTitle()));

        return $this->redirect($this->adminUrlGenerator
            ->setController(PostCrudController::class)
            ->setAction(Action::INDEX)
            ->generateUrl());
    }
}
