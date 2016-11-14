<?php

namespace GPS\AppBundle\Form;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * This is the registration form defintion.
 *
 * @author Evan Villemez
 */
class RegistrationType extends AbstractType
{
    public function getName()
    {
        return 'registration';
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstName', 'text', ['constraints' => [new Assert\NotBlank()]]);
        $builder->add('lastName', 'text', ['constraints' => [new Assert\NotBlank()]]);
        $builder->add('email', 'email', ['constraints' => [new Assert\NotBlank(), new Assert\Email()]]);

        $builder->add('password_confirm', 'repeated', [
           'type'        => 'password',
           'first_name'  => 'password',
           'second_name' => 'confirm',
           'invalid_message' => 'The password fields must match, and be at least 8 characters long.',
           'options' => [
               'error_bubbling' => true,
            ],
           'first_options' => [
               'label' => "Password",
               'constraints' => [
                   new Assert\NotBlank(),
                   new Assert\Length(['min' => 8, 'minMessage' => "Your password must contain at least {{ limit }} characters."])
               ]
           ],
           'second_options' => ['label' => "Confirm Password"],
           'error_mapping' => [
               'password' => 'password_confirm',
               'confirm' => 'password_confirm'
            ]
        ]);
        
        $builder->add('acceptedTerms', 'checkbox', [
            'constraints' => [new Assert\NotBlank(), new Assert\True()]
        ]);
    }
}
