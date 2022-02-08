<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\DublinCoreBundle\Form;

use Nines\DublinCoreBundle\Entity\Element;
use Nines\UtilBundle\Form\TermType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Element form.
 */
class ElementType extends TermType {
    /**
     * Add form fields to $builder.
     *
     * @param array<string,mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        parent::buildForm($builder, $options);
        $builder->add('uri', UrlType::class, [
            'label' => 'Uri',
            'required' => true,
            'attr' => [
                'help_block' => '',
            ],
        ]);
        $builder->add('comment', TextareaType::class, [
            'label' => 'Comment',
            'required' => true,
            'attr' => [
                'help_block' => '',
                'class' => 'tinymce',
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
            'data_class' => Element::class,
        ]);
    }
}
