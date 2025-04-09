<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Participant;

class AdminUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('pseudo', TextType::class)
            ->add('email', EmailType::class)
            ->add('telephone', TextType::class)
            ->add('motDePasse', PasswordType::class)
            ->add('campus', null)
            ->add('administrateur', ChoiceType::class, [
                'choices' => [
                    'Non' => false,
                    'Oui' => true,
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => 'Administrateur ?',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Créer le compte'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
