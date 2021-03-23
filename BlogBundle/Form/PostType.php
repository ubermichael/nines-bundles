<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\BlogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * PostType form.
 */
class PostType extends AbstractType
{
    /**
     * Add form fields to $builder.
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        $builder->add('title', null, [
            'label' => 'Title',
            'required' => true,
            'attr' => [
                'help_block' => '',
            ],
        ]);
        $builder->add('category');
        $builder->add('status');
        $builder->add('excerpt', TextareaType::class, [
            'label' => 'Excerpt',
            'required' => false,
            'attr' => [
                'help_block' => '',
                'class' => 'tinymce',
            ],
        ]);
        $builder->add('content', TextareaType::class, [
            'label' => 'Content',
            'required' => true,
            'attr' => [
                'help_block' => '',
                'class' => 'tinymce',
            ],
        ]);
        $builder->add('includeComments', ChoiceType::class, [
            'label' => 'Include Comments',
            'expanded' => true,
            'multiple' => false,
            'choices' => [
                'Yes' => true,
                'No' => false,
            ],
            'required' => true,
            'placeholder' => false,
            'attr' => [
                'help_block' => '',
            ],
        ]);
    }

    /**
     * Define options for the form.
     *
     * Set default, optional, and required options passed to the
     * buildForm() method via the $options parameter.
     */
    public function configureOptions(OptionsResolver $resolver) : void {
        $resolver->setDefaults([
            'data_class' => 'Nines\BlogBundle\Entity\Post',
        ]);
    }
}
