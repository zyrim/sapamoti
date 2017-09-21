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
            ->add('_description', TextareaType::class, ['label' => 'Beschreibung'])
            ->add('_amount', NumberType::class, ['label' => 'Betrag'])
            ->add('_date', DateType::class, ['label' => 'Datum'])
            ->add('_fixed', CheckboxType::class, ['label' => 'Regelmäßig', 'required' => false])
            ->add('_save', SubmitType::class, ['label' => 'Hinzufügen'])
        ;
    }
}