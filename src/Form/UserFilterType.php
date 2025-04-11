<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserFilterType extends AbstractType
{
public function buildForm(FormBuilderInterface $builder, array $options): void
{
$builder
->add('search', SearchType::class, [
'required' => false,
'label' => 'Rechercher',
'attr' => ['placeholder' => 'Nom, prénom, pseudo, email...'],
])
->add('actif', ChoiceType::class, [
'required' => false,
'label' => 'Statut',
'placeholder' => 'Tous',
'choices' => [
'Actif' => 1,
'Inactif' => 0,
],
])
->add('admin', ChoiceType::class, [
'required' => false,
'label' => 'Administrateur',
'placeholder' => 'Tous',
'choices' => [
'Oui' => 1,
'Non' => 0,
],
]);
}

public function configureOptions(OptionsResolver $resolver): void
{
$resolver->setDefaults([]);
}
}
