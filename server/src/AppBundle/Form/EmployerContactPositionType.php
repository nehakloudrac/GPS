<?php

namespace GPS\AppBundle\Form;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * This is the employer contact form defintion.
 *
 * @author Evan Villemez
 */
class EmployerContactPositionType extends AbstractType
{
    private $languages;
    private $countries;

    public function __construct($langs, $countries)
    {
        $this->languages = $langs;
        $this->countries = $countries;
    }

    public function getName()
    {
        return 'employer_contact_position';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'GPS\AppBundle\Document\EmployerContactPosition',
        ));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title');
        $builder->add('city');
        $builder->add('country', 'choice', [
            'choices' => $this->countries
        ]);

        $builder->add('status', 'choice', [
            'choices' => [
                'full_time' => "Full time",
                'part_time' => "Part time",
                'internship_paid' => "Paid internship",
                'internship_unpaid' => "Unpaid internship",
                'project' => "Project-based"
            ]
        ]);
        $builder->add('startDate', 'date', [
            'input' => 'datetime',
            'widget' => 'choice'
        ]);
        $builder->add('salary');

        $builder->add('desiredLanguages', 'choice', [
            'multiple' => true,
            'choices' => $this->languages
        ]);
        $builder->add('desiredCountries', 'choice', [
            'multiple' => true,
            'choices' => $this->countries
        ]);
    }
}
