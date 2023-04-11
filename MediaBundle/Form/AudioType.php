<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Form;

use Nines\MediaBundle\Entity\Audio;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Audio form.
 */
class AudioType extends AbstractFileType {
    /**
     * Add form fields to $builder.
     *
     * @param null|mixed $label
     */
    public function buildForm(FormBuilderInterface $builder, array $options, $label = null) : void {
        parent::buildForm($builder, $options, 'Audio File');
    }

    /**
     * Define options for the form.
     *
     * Set default, optional, and required options passed to the
     * buildForm() method via the $options parameter.
     */
    public function configureOptions(OptionsResolver $resolver) : void {
        $resolver->setDefaults([
            'data_class' => Audio::class,
        ]);
    }
}
