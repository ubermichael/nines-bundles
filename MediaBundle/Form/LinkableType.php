<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Form;

use Nines\MediaBundle\Entity\Link;
use Nines\MediaBundle\Entity\LinkableInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Link form.
 */
class LinkableType {

    public static function add(FormBuilderInterface $builder, $options) {
        $entity = $options['data'];
        if( ! $entity instanceof LinkableInterface) {
            throw new UnexpectedTypeException($entity, LinkableInterface::class);
        }
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
            'data' => $options['data']->getLinks(),
        ]);
    }

}
