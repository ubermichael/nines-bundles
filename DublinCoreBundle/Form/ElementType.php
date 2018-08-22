<?php

namespace Nines\DublinCoreBundle\Form;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Nines\DublinCoreBundle\Entity\Element;
use Nines\UtilBundle\Form\TermType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ElementType form.
 */
class ElementType extends TermType {

    /**
     * Add form fields to $builder.
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $builder->add('uri', UrlType::class, array(
            'label' => 'Uri',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
        $builder->add('comment', CKEditorType::class, array(
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
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => Element::class,
        ));
    }

}
