<?php

namespace App\Form;

use App\Entity\ShopOrder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', HiddenType::class)
            ->add('sessionId', HiddenType::class)
            ->add(
                'userName',
                TextType::class,
                [
                    'label' => 'Имя',
                ]
            )
            ->add(
                'userEmail',
                EmailType::class,
                [
                    'label' => 'email',
                ]
            )
            ->add(
                'userPhone',
                TextType::class,
                [
                    'label' => 'Телефон',
                ]
            )
            ->add(
                'save',
                SubmitType::class,
                [
                    'label' => 'Сохранить',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => ShopOrder::class,
            ]
        );
    }
}
