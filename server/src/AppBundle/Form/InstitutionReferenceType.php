<?php

namespace GPS\AppBundle\Form;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * This is institution reference that's embedded in the Employer Contact form.
 *
 * @author Evan Villemez
 */
class InstitutionReferenceType extends AbstractType
{
    private $industries;

    public function __construct($industries)
    {
        $this->industries = $industries;
    }

    public function getName()
    {
        return 'institution_reference';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'GPS\AppBundle\Document\Candidate\InstitutionReference',
        ));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name');
        $builder->add('industries', new TaggingType(), [
            'choices' => $this->industries
        ]);
    }
}
