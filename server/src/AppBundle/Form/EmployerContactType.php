<?php

namespace GPS\AppBundle\Form;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue as RecaptchaTrue;

/**
 * This is the employer contact form defintion.
 *
 * @author Evan Villemez
 */
class EmployerContactType extends AbstractType
{
    private $industries;
    private $languages;
    private $countries;
    
    public function __construct($industries, $languages, $countries)
    {
        $this->industries = $industries;
        $this->languages = $languages;
        $this->countries = $countries;
    }
    
    public function getName()
    {
        return 'employer_contact';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'GPS\AppBundle\Document\EmployerContact',
        ]);
    }
        
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstName');
        $builder->add('lastName');
        $builder->add('email');
        $builder->add('phoneNumber');
        $builder->add('title');
        $builder->add('institution', new InstitutionReferenceType($this->industries));
        $builder->add('numEmployees');
        $builder->add('numAnnualHires');
        $builder->add('numPositions');

        $builder->add('positions', 'collection', array(
            'type'         => new EmployerContactPositionType($this->languages, $this->countries),
            'allow_add'    => true,
        ));
        
        // Google reCaptcha support
        $builder->add('recaptcha', 'ewz_recaptcha', array(
            'attr'        => array(
                'options' => array(
                    'theme' => 'light',
                    'type'  => 'image'
                )
            ),
            'mapped'      => false,
            'error_bubbling' => false,
            'invalid_message' => 'You must click on the captcha box before submission.',
            'constraints' => array(
                new RecaptchaTrue()
            )
        ));

        $builder->add('addPosition', 'submit', ['label' => 'Add Position']);
        $builder->add('save', 'submit', ['label' => "Submit"]);
    }
}
