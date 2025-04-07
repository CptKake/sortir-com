<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
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
                'widget' => 'time_widget',
            ])
            ->add('duree', IntegerType::class, [
				'label' => 'duree',
	            'attr' => [
					'placeholder' => 'durée en min',
	            ]
            ])
            ->add('dateLimiteInscription', DateTimeType::class, [
				'label' => 'Limite d\'inscription',
	            'widget' => 'time_widget',
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
            ->add('organisateur', EntityType::class, [
                'class' => Participant::class,
                'choice_label' => 'id',
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'id',
            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'id',
            ])
            ->add('etat', EntityType::class, [
                'class' => Etat::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
