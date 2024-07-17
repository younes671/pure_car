<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true, 
                'attr' => [
                'class' => 'field',
                'id' => 'name']
            ])
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo',
                'required' => true, 
                'attr' => [
                'class' => 'field',]
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'required' => true, 
                'attr' => [
                'class' => 'field',]
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prenom',
                'required' => true, 
                'attr' => [
                'class' => 'field',]
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'required' => true, 
                'attr' => [
                'class' => 'field',]
            ])
            ->add('cp', TextType::class, [
                'label' => 'Cp',
                'required' => true, 
                'attr' => [
                'class' => 'field'],
                'constraints' => [
                    new Regex("/^\d+$/")
                ]
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'required' => true, 
                'attr' => [
                'class' => 'field',]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
