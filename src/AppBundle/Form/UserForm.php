<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class UserForm
 *
 * @package AppBundle\Form
 */
class UserForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('_firstname', TextType::class, ['label' => 'Vorname', 'required' => false])
            ->add('_lastname', TextType::class, ['label' => 'Nachname', 'required' => false])
            ->add('_email', EmailType::class, ['label' => 'Email'])
            ->add('save', SubmitType::class, ['label' => 'Speichern'])
        ;
    }
}