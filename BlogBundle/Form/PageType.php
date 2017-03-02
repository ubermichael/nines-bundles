<?php

namespace Nines\BlogBundle\Form;

use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Blog page form type.
 */
class PageType extends AbstractType
{
    /**
     * Build the form.
     * 
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {    
        $builder->add('title');     
        $builder->add('weight');     
        $builder->add('public');     
        $builder->add('excerpt', CKEditorType::class, array(
            'attr' => array(
                'help_block' => 'Excerpts will be shown on the home page and in '
                . 'lists of pages. Leave this field blank and one will be '
                . 'generated automatically.'
            ),
        ));
        $builder->add('content', CKEditorType::class, array(
        ));     
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Nines\BlogBundle\Entity\Page'
        ));
    }
}
