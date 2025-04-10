<?php
// src/Form/SortieFilterType.php

namespace App\Form;

use App\Entity\Campus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieFilterType extends AbstractType
{
public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder->add('campus', EntityType::class, [
        'class' => Campus::class,
        'choice_label' => 'nom',
        'required' => false,
        'placeholder' => 'Liste des campus',
    ])

->add('search', SearchType::class, [
'required' => false,
'label' => 'Le nom de la sortie contient',
])
->add('dateDebut', DateType::class, [
'required' => false,
'widget' => 'single_text',
'label' => 'Entre',
])
->add('dateFin', DateType::class, [
'required' => false,
'widget' => 'single_text',
'label' => 'et',
])
->add('organisateur', CheckboxType::class, [
'required' => false,
'label' => "Sorties dont je suis l'organisateur/trice",
])
->add('inscrit', CheckboxType::class, [
'required' => false,
'label' => "Sorties auxquelles je suis inscrit/e",
])
->add('nonInscrit', CheckboxType::class, [
'required' => false,
'label' => "Sorties auxquelles je ne suis pas inscrit/e",
])
->add('passees', CheckboxType::class, [
'required' => false,
'label' => "Sorties passées",
]);
}

public function configureOptions(OptionsResolver $resolver): void
{
$resolver->setDefaults([
'method' => 'GET',
'csrf_protection' => false, // important pour les filtres GET
]);
}
}
