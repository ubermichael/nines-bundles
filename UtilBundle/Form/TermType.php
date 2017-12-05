<?php

namespace Nines\UtilBundle\Form;

use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class TermType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('name', TextType::class, array(
            'label' => 'Name',
            'attr' => array(
                'help_block' => 'A computer-readable name. Should be lowercase without spaces.'
            ),
        ));
        $builder->add('label', TextType::class, array(
            'label' => 'Label',
            'attr' => array(
                'help_block' => 'A human-readable name.'
            ),
        ));
        $builder->add('description', CKEditorType::class);
    }

}
