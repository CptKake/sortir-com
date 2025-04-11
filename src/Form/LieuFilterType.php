<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;

class LieuFilterType extends AbstractType
{
public function buildForm(FormBuilderInterface $builder, array $options)
{
$builder
->add('search', SearchType::class, [
'label' => false,
'required' => false,
'attr' => [
'placeholder' => 'Rechercher un lieu...',
'class' => 'input'
]
]);
}
}
