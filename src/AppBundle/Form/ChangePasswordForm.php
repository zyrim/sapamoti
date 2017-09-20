<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ChangePasswordForm
 *
 * @package AppBundle\Form
 */
class ChangePasswordForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('_oldPassword', PasswordType::class, ['label' => 'Altes Password'])
            ->add('_newPassword', PasswordType::class, ['label' => 'Neues Password'])
            ->add('_repeatNewPassword', PasswordType::class, ['label' => 'Wiederholung'])
            ->add('_save', SubmitType::class, ['label' => 'Speichern'])
        ;
    }
}