<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Vich\UploaderBundle\Form\Type\VichImageType;

class UserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email Address',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Email(['message' => 'Please enter a valid email address.']),
                ],
            ])
            ->add('name', TextType::class, [
                'required' => false,
                'label' => 'First Name',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('surname', TextType::class, [
                'required' => false,
                'label' => 'Last Name',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Remove current image',
                'download_uri' => true,
                'image_uri' => false,
                'label' => 'Profile Image',
                'attr' => ['class' => 'form-control']
            ])
            ->add('plainPassword', PasswordType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'New Password (leave blank to keep current)',
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'form-control',
                    'placeholder' => 'Enter new password or leave blank'
                ],
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Password should be at least {{ limit }} characters',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
