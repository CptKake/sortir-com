<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use function PHPUnit\Framework\exactly;

class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du lieu',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir un nom de lieu.']),
                    new Length(['max' => 255, 'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.'])
                ]
            ])
            ->add('rue', TextType::class, [
                'label' => 'Rue',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir une rue.']),
                    new Length(['max' => 255, 'maxMessage' => 'La rue ne peut pas dépasser {{ limit }} caractères.'])
                ]
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir une ville.']),
                    new Length(['max' => 255, 'maxMessage' => 'La ville ne peut pas dépasser {{ limit }} caractères.'])
                ]
            ])
            ->add('codePostal', TextType::class, [
                'label' => 'Code postal',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir un code postal.']),
                    new Length(['max' => 5, 'maxMessage' => 'Le code postal doit contenir {{ limit }} chiffres.']),
                    new Regex([
                        'pattern' => '/^[0-9]{5}$/',
                        'message' => 'Le code postal doit contenir 5 chiffres.'
                    ])
                ]
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'Latitude',
                'required' => false,
                'scale' => 6,
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'Longitude',
                'required' => false,
                'scale' => 6,
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
