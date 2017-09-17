<?php
/**
 * AppBundle
 *
 * @namespace
 */
namespace AppBundle\Form;

use Symfony\Component\Form\{AbstractType, FormBuilderInterface};
use Symfony\Component\Form\Extension\Core\Type\{EmailType, PasswordType, SubmitType, TextType};

/**
 * Class UserForm
 *
 * @package AppBundle\Form
 */
class UserForm extends AbstractType
{
    /**
     * Build the form.
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (isset($options['form_type']) && $options['form_type'] == 'login') {
            $builder
                ->add('email', EmailType::class, ['label' => 'Email'])
                ->add('password', PasswordType::class, ['label' => 'Passwort']);
        } else {
            $builder
                ->add('firstname', TextType::class, ['label' => 'Vorname'])
                ->add('lastname', TextType::class, ['label' => 'Nachname'])
                ->add('email', EmailType::class, ['label' => 'Email'])
                ->add('password', PasswordType::class, ['label' => 'Passwort'])
                ->add('repeat', PasswordType::class, ['label' => 'Passwort wiederholen', 'mapped' => false, 'required' => true])
                ->add('submit', SubmitType::class, ['label' => 'Registrieren']);
        }

        parent::buildForm($builder, $options);
    }
}