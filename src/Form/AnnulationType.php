<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AnnulationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('motifAnnulation', TextareaType::class, [
                'label' => 'Motif d\'annulation',
                'attr' => [
                    'class' => 'textarea',
                    'placeholder' => 'Veuillez indiquer pourquoi vous annulez cette sortie...',
                    'rows' => 5
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez indiquer un motif d\'annulation'
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([

        ]);
    }
}