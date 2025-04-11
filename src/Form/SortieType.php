<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
				'label' => 'Nom',
	            'attr' => [
					'placeholder' => 'Nom',
	            ]
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
				'label' => 'Début',
                'widget' => 'single_text',
            ])
            ->add('duree', IntegerType::class, [
				'label' => 'duree',
	            'attr' => [
					'placeholder' => 'durée en min',
	            ]
            ])
            ->add('dateLimiteInscription', DateTimeType::class, [
				'label' => 'Limite d\'inscription',
	            'widget' => 'single_text',
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
				'label' => 'Nombre de participants maximum',
            ])
            ->add('infosSortie', TextareaType::class, [
				'label' => 'Information sortie',
	            'attr' => [
					'placeholder' => 'Votre description ici...',
	            ]
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
            ])
            ->add('lieu', LieuType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
