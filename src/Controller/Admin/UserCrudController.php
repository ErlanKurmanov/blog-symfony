<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Enum\UserRole;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User')
            ->setEntityLabelInPlural('Users')
            ->setSearchFields(['email', 'name', 'surname'])
            ->setDefaultSort(['id' => 'DESC'])
            ->showEntityActionsInlined()
            ->setPaginatorPageSize(25);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            EmailField::new('email')
                ->setColumns(6),

            TextField::new('name')
                ->setColumns(3),

            TextField::new('surname')
                ->setColumns(3),

            ChoiceField::new('role')
                ->setColumns(6)
                ->setChoices([
                    'User' => UserRole::USER,
                    'Admin' => UserRole::ADMIN,
                ])
                ->renderAsBadges([
                    UserRole::USER->value => 'primary',
                    UserRole::ADMIN->value => 'success',
                ])
                ->formatValue(function ($value) {
                    return $value instanceof \BackedEnum ? $value->value : (string) $value;
                }),

            BooleanField::new('isVerified')
                ->setColumns(6)
                ->renderAsSwitch(false),

            ImageField::new('profileImage')
                ->setBasePath('/images/profiles/')
                ->setUploadDir('public/images/profiles')
                ->hideOnForm()
                ->setColumns(3),

            IntegerField::new('posts.count', 'Posts Count')
                ->hideOnForm()
                ->setColumns(3)
                ->formatValue(function ($value, $entity) {
                    return $entity->getPosts()->count();
                }),

            IntegerField::new('followers.count', 'Followers')
                ->hideOnForm()
                ->setColumns(3)
                ->formatValue(function ($value, $entity) {
                    return $entity->getFollowers()->count();
                }),

            IntegerField::new('following.count', 'Following')
                ->hideOnForm()
                ->setColumns(3)
                ->formatValue(function ($value, $entity) {
                    return $entity->getFollowing()->count();
                }),

            AssociationField::new('posts')
                ->hideOnIndex()
                ->hideOnForm()
                ->setTemplatePath('admin/user_posts.html.twig'),

            DateTimeField::new('updatedAt')
                ->hideOnIndex()
                ->hideOnForm()
                ->setFormat('dd/MM/yyyy HH:mm'),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
//            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->displayIf(static function (User $user) {
                    // Only allow deleting non-admin users
                    return $user->getRole() !== UserRole::ADMIN;
                });
            });
    }
}
