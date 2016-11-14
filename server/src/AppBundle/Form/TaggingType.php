<?php

namespace GPS\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * A utility class used by the TaggingType for storing the available
 * choices, as well as actual selections.  This gets updated
 * at several points during the form lifecycle.
 */
class TaggingTypeChoices
{
    public $choices = [];
    public $selectedMap = [];
}

/**
 * WARNING: This implementation is likely incomplete and should not be used in contexts
 * other than the EmployerContact form without additional refactoring.
 *
 * Custom basic implementation of a multiselect that ignores the actual
 * choices, thus allowing free form tagging provided by Select2.
 */
class TaggingType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'choices' => [],
            'selected' => new TaggingTypeChoices(),
            'allow_extra_fields' => true
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // initialize choices with anything passed from
        // the field definition
        $options['selected']->choices = $options['choices'];

        // at multiple steps, existing, or submitted values that are
        // not already in the choice list needed to be added into the choice list
        //
        // this will update the TaggingTypeChoices object in $options directly
        $mergeValuesIntoChoices  = function($selections) use ($options) {
            $options['selected']->selectedMap = array_flip($selections);

            $widgetChoiceMap = array_flip($options['choices']);

            // keep track of selections for use in the template
            // and put choice into choices list
            foreach ($selections as $selection) {
                $widgetChoiceMap[$selection] = true;
            }

            // new choices include selected values
            $options['selected']->choices = array_keys($widgetChoiceMap);
        };

        // merge existing values from the model in to the choices list
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $e) use ($mergeValuesIntoChoices) {
            $existingValues = $e->getData();

            if (is_array($existingValues)) {
                $mergeValuesIntoChoices($existingValues);
            }
        });

        // merge submitted values from the form into the choices list
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $e) use ($mergeValuesIntoChoices) {
            $submittedValues = $e->getData();

            if (is_array($submittedValues)) {
                $mergeValuesIntoChoices($submittedValues);
            }
        });

        // during the actual submission, set the normalized values as whatever
        // was stored in the selection map
        //
        // NOTE: I'm not convinced this is the correct way to do all this... but this is
        // what I arrived at by process of elimination
        $builder->addEventListener(FormEvents::SUBMIT, function(FormEvent $e) use ($options) {
            $e->setData(array_keys($options['selected']->selectedMap));
        });
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['full_name'] .= '[]';
        $view->vars['selected'] = $options['selected'];
    }

    public function getName()
    {
        return 'tagging';
    }
}
