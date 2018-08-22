<?php

namespace Nines\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * UserType form.
 */
class UserType extends AbstractType {

    /**
     * Add form fields to $builder.
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('fullname', null, array(
            'label' => 'Fullname',
            'required' => false,
            'attr' => array(
                'help_block' => '',
            ),
        ));
        $builder->add('institution', null, array(
            'label' => 'Institution',
            'required' => false,
            'attr' => array(
                'help_block' => '',
            ),
        ));
        $builder->add('data', null, array(
            'label' => 'Data',
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
            'data_class' => 'Nines\UserBundle\Entity\User'
        ));
    }

}
