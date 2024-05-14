<?php

namespace App\Form;

use App\Entity\Modele;
use App\Entity\Vehicule;
use App\Entity\Categorie;
use Doctrine\DBAL\Types\BlobType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use App\Form\VehiculeStreamToFileTransformer;


class VehiculeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('autonomie', NumberType::class, [
                'attr' => ['class' => 'field']
            ])
            ->add('nbPorte', NumberType::class, [
                'attr' => ['class' => 'field']
            ])
            ->add('nbPlace', NumberType::class, [
                'attr' => ['class' => 'field']
            ])
           
            ->add('nbBagage', NumberType::class, [
                'attr' => ['class' => 'field']
            ])
            ->add('prix', NumberType::class, [
                'attr' => ['class' => 'field']
            ])
            ->add('img', FileType::class, [
                'data_class' => null
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'attr' => ['class' => 'field']
            ])
            ->add('modele', EntityType::class, [
                'class' => Modele::class,
                'choice_label' => 'nom',
                'attr' => ['class' => 'field']
            ])
            ->add('bluetooth', CheckboxType::class, [
                'attr' => ['class' => 'field'],
                'required' => false
            ])
            ->add('climatisation', CheckboxType::class, [
                'attr' => ['class' => 'field'],
                'required' => false
            ])
            ->add('gps', CheckboxType::class, [
                'attr' => ['class' => 'field'],
                'required' => false
            ])
            ->add('valider', SubmitType::class, [
                'attr' => [
                    'class' => 'button'
                ]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vehicule::class,
        ]);
    }
}
