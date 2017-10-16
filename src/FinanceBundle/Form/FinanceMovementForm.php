<?php

namespace FinanceBundle\Form;

use FinanceBundle\Entity\FinanceMovement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\{
    TextareaType, NumberType, DateType, CheckboxType, SubmitType
};
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class FinanceMovementForm
 *
 * @package FinanceBundle
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

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            if ($data instanceof FinanceMovement && $data->getFinanceMovementId()) {
                $form
                    ->add('_editAmount', NumberType::class, [
                    'label'    => 'Betrag erweitern',
                    'required' => false,
                    'mapped'   => false
                    ])
                    ->add('_remove', SubmitType::class, [
                        'label' => 'Entfernen',
                        'attr' => ['class' => 'btn btn-danger']
                    ]);
            }
        });
    }
}