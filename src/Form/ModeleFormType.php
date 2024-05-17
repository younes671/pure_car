<?php

namespace App\Form;

use App\Entity\Marque;
use App\Entity\Modele;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ModeleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $marques = $options['marques']; // Récupérer les marques triées
        
        $builder
            ->add('nom', TextType::class, [
                'attr' => ['class' => 'field']
            ])
            ->add('marque', EntityType::class, [
                'class' => Marque::class,
                'choices' => $marques, // Utiliser les marques triées comme options
                'choice_label' => 'nom',
                'placeholder' => 'Choisir une marque',
                'attr' => ['class' => 'field']
            ])
            ->add('valider', SubmitType::class, [
                'attr' => [
                    'class' => 'button'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Modele::class,
            'marques' => [], // Définir une option pour les marques triées
        ]);
    }
}
