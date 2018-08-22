<?php

namespace Nines\DublinCoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ElementType form.
 */
class ElementType extends AbstractType
{
    /**
     * Add form fields to $builder.
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {        $builder->add('uri', null, array(
            'label' => 'Uri',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('comment', null, array(
            'label' => 'Comment',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
        
    }
    
    /**
     * Define options for the form.
     *
     * Set default, optional, and required options passed to the
     * buildForm() method via the $options parameter.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Nines\DublinCoreBundle\Entity\Element'
        ));
    }

}
