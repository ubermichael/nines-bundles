<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class TermType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        $builder->add('label', TextType::class, [
            'label' => 'Label',
            'attr' => [
                'help_block' => 'A human-readable name.',
            ],
        ]);
        $builder->add('description', TextareaType::class, [
            'required' => false,
            'attr' => [
                'class' => 'tinymce',
                'help_block' => 'A simple description of the item.',
            ],
        ]);
    }
}
