<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\DublinCoreBundle\Form\Mapper;

use Doctrine\ORM\EntityManagerInterface;
use Nines\DublinCoreBundle\Entity\Element;
use Nines\DublinCoreBundle\Entity\Value;
use Nines\DublinCoreBundle\Entity\ValueInterface;
use Nines\DublinCoreBundle\Repository\ElementRepository;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;

class DublinCoreMapper extends PropertyPathMapper implements DataMapperInterface {
    private ?ElementRepository $elementRepo = null;

    private ?EntityManagerInterface $em = null;

    private bool $parentCall = true;

    public function setParentCall(bool $call) : void {
        $this->parentCall = $call;
    }

    public function mapDataToForms($viewData, $forms) : void {
        if ( ! $viewData instanceof ValueInterface) {
            return;
        }
        if ($this->parentCall) {
            parent::mapDataToForms($viewData, $forms);
        }
        $forms = iterator_to_array($forms);
        foreach ($this->elementRepo->findAll() as $element) {
            // @var Element $element
            $forms[$element->getName()]->setData($viewData->getValues($element->getName()));
        }
    }

    public function mapFormsToData($forms, &$viewData) : void {
        if ( ! $viewData instanceof ValueInterface) {
            return;
        }
        if ($this->parentCall) {
            parent::mapFormsToData($forms, $viewData);
        }
        if ( ! $this->em->contains($viewData)) {
            $this->em->persist($viewData);
            $this->em->flush();
        }
        $forms = iterator_to_array($forms);
        foreach ($viewData->getValues() as $value) {
            $this->em->remove($value);
        }
        foreach ($this->elementRepo->findAll() as $element) {
            foreach ($forms[$element->getName()]->getData() as $data) {
                $value = new Value();
                $value->setEntity($viewData);
                $value->setData($data);
                $value->setElement($element);
                $this->em->persist($value);
            }
        }
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setEntityManager(EntityManagerInterface $em) : void {
        $this->em = $em;
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setElementRepository(ElementRepository $repo) : void {
        $this->elementRepo = $repo;
    }
}
