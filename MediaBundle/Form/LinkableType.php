<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Form;

use Nines\MediaBundle\Entity\Link;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Link form.
 */
class LinkableType {
    /**
     * @param mixed $options
     */
    public static function add(FormBuilderInterface $builder, $options) : void {
        $builder->add('links', CollectionType::class, [
            'label' => 'Links',
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'entry_type' => LinkType::class,
            'entry_options' => [
                'label' => false,
            ],
            'attr' => [
                'class' => 'collection collection-complex',
                'help_block' => '',
            ],
            'mapped' => false,
        ]);
    }
}
