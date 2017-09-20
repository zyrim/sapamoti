<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\{
    TextareaType, NumberType, DateType, CheckboxType, SubmitType
};

/**
 * Class FinanceMovementForm
 *
 * @package AppBundle\Form
 */
class FinanceMovementForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', TextareaType::class, ['label' => 'Beschreibung'])
            ->add('amount', NumberType::class, ['label' => 'Betrag'])
            ->add('date', DateType::class, ['label' => 'Datum'])
            ->add('fixed', CheckboxType::class, ['label' => 'Regelmäßig', 'required' => false])
            ->add('save', SubmitType::class, ['label' => 'Hinzufügen'])
        ;
    }
}