<?php

namespace App\Form;

use App\Entity\Robot;
use App\Enum\Locomotion;
use App\Enum\Weapon;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RobotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('owner', TextType::class)
            ->add('weapon', EnumType::class, [
                'class' => Weapon::class,
                'choice_label' => fn (Weapon $w) => $w->label(),
                'placeholder' => 'Choose a weapon',
            ])
            ->add('locomotion', EnumType::class, [
                'class' => Locomotion::class,
                'choice_label' => fn (Locomotion $l) => $l->label(),
                'placeholder' => 'Choose locomotion type',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Robot::class,
        ]);
    }
}
