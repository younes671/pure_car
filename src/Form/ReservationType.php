<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Facture;
use App\Entity\Vehicule;
use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'field']
            ])
            ->add('nom', TextType::class, [
                'attr' => ['class' => 'field']
            ])
            ->add('prenom', TextType::class, [
                'attr' => ['class' => 'field']
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'field date']
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'field date']
            ])
            ->add('adresse', TextType::class, [
                'attr' => ['class' => 'field']
            ])
            ->add('cp', TextType::class, [
                'attr' => ['class' => 'field']
            ])
            ->add('ville', TextType::class, [
                'attr' => ['class' => 'field']
            ])
            // ->add('vehicule', EntityType::class, [
            //     'class' => Vehicule::class,
            //     'choice_label' => 'nom',
            // ])
            // ->add('facture', EntityType::class, [
            //     'class' => Facture::class,
            //     'choice_label' => 'id',
            // ])
            // ->add('user', EntityType::class, [
            //     'class' => User::class,
            //     'choice_label' => 'id',
            // ])
            ->add('valider', SubmitType::class, [
                'attr' => [
                    'class' => 'button'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
