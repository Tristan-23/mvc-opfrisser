<?php

namespace App\Form;

use App\Dto\Input\BattleScoreInput;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BattleScoreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $participants = $options['participants'];

        $choices = [];
        foreach ($participants as $participant) {
            $robot = $participant->getRobot();
            $choices[$robot->getName()] = $participant->getId();
        }

        $builder
            ->add('winner', ChoiceType::class, [
                'choices' => $choices,
                'expanded' => true,
                'label' => 'Select the winner',
            ])
            ->add('isKnockout', CheckboxType::class, [
                'required' => false,
                'label' => 'Won by knockout?',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BattleScoreInput::class,
            'participants' => [],
        ]);
    }
}
