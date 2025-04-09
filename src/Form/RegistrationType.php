<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{
public function buildForm(FormBuilderInterface $builder, array $options): void
{
$builder
->add('nom')
->add('prenom')
->add('pseudo')
    ->add('email', EmailType::class, [
        'attr' => [
            'class' => 'input',
            'pattern' => '.*',
        ],
        'label' => 'Email'
    ])

->add('telephone')

->add('plainPassword', PasswordType::class, [
'label' => 'Mot de passe',
'mapped' => false,
'attr' => ['autocomplete' => 'new-password'],
])
->add('campus', EntityType::class, [
'class' => Campus::class,
'choice_label' => 'nom',
'label' => 'Campus de rattachement',
]);
}

public function configureOptions(OptionsResolver $resolver): void
{
$resolver->setDefaults([
'data_class' => Participant::class,
]);
}
}
