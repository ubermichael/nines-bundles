<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\DublinCoreBundle\Form;

use Nines\DublinCoreBundle\Form\Mapper\DublinCoreMapper;
use Nines\DublinCoreBundle\Entity\Element;
use Nines\DublinCoreBundle\Repository\ElementRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\DataTransformer\EntityToPropertyTransformer;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

/**
 * Document form.
 */
abstract class ValueType extends AbstractType {
    /**
     * @var ElementRepository
     */
    private ElementRepository $repo;

    /**
     * Add form fields to $builder.
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void {

        foreach($this->repo->indexQuery()->execute() as $element) {
            /** @var Element $element */
            $builder->add($element->getName(), CollectionType::class, [
                'label' => $element->getLabel(),
                'entry_type' => TextType::class,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'entry_options' => [
                    'label' => false,
                ],
                'attr' => [
                    'help_block' => $element->getDescription(),
                    'class' => 'collection-simple',
                ],
                'mapped' => false,
            ]);
        }
    }

    /**
     * @param ElementRepository $repo
     * @required
     */
    public function setElementRepository(ElementRepository $repo) {
        $this->repo = $repo;
    }

}
