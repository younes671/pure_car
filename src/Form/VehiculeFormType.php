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
        $categories = $options['categories']; // Récupérer les marques triées
        $modeles = $options['modeles']; // Récupérer les marques triées

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
                'attr' => ['class' => 'field'],
                'required' => false
            ])
            ->add('prix', NumberType::class, [
                'attr' => ['class' => 'field']
            ])
            ->add('img', FileType::class, [
                'data_class' => null
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choices' => $categories, // Utiliser les marques triées comme options
                'choice_label' => 'nom',
                'placeholder' => 'Choisir une catégorie',
                'attr' => ['class' => 'field']
            ])
            ->add('modele', EntityType::class, [
                'class' => Modele::class,
                'choices' => $modeles, 
                'choice_label' => 'nom',
                'placeholder' => 'Choisir un modèle',
                'attr' => ['class' => 'field']
            ])
            ->add('bluetooth', CheckboxType::class, [
                'label_attr' => ['class' => 'field-checkbox'],
                'attr' => ['class' => 'field-checkbox'],
                'required' => false
            ])
            ->add('climatisation', CheckboxType::class, [
                'label_attr' => ['class' => 'field-checkbox'],
                'attr' => ['class' => 'field-checkbox'],
                'required' => false
            ])
            ->add('gps', CheckboxType::class, [
                'label_attr' => ['class' => 'field-checkbox'],
                'attr' => ['class' => 'field-checkbox'],
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
            'categories' => [], // Définir une option pour les marques triées
            'modeles' => [], // Définir une option pour les marques triées
        ]);
    }
}
