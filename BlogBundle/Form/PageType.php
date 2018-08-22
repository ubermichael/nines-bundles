<?php

namespace Nines\BlogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * PageType form.
 */
class PageType extends AbstractType
{
    /**
     * Add form fields to $builder.
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {        $builder->add('weight', null, array(
            'label' => 'Weight',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('public', ChoiceType::class, array(
            'label' => 'Public',
            'expanded' => true,
            'multiple' => false,
            'choices' => array(
                'Yes' => true,
                'No' => false,
                ),
            'required' => true,
            'placeholder' => false,
            'attr' => array(
                'help_block' => '',
            ),

        ));
                $builder->add('includeComments', ChoiceType::class, array(
            'label' => 'Include Comments',
            'expanded' => true,
            'multiple' => false,
            'choices' => array(
                'Yes' => true,
                'No' => false,
                ),
            'required' => true,
            'placeholder' => false,
            'attr' => array(
                'help_block' => '',
            ),

        ));
                $builder->add('title', null, array(
            'label' => 'Title',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('excerpt', null, array(
            'label' => 'Excerpt',
            'required' => false,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('content', null, array(
            'label' => 'Content',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('searchable', null, array(
            'label' => 'Searchable',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                        $builder->add('user');
        
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
            'data_class' => 'Nines\BlogBundle\Entity\Page'
        ));
    }

}
