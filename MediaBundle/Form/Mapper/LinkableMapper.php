<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Form\Mapper;

use Doctrine\ORM\EntityManagerInterface;
use Nines\DublinCoreBundle\Repository\ElementRepository;
use Nines\MediaBundle\Entity\Link;
use Nines\MediaBundle\Entity\LinkableInterface;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;
use Symfony\Component\Form\Form;

class LinkableMapper extends PropertyPathMapper implements DataMapperInterface {
    private EntityManagerInterface $em;

    public function mapDataToForms($viewData, $forms) : void {
        if ( ! $viewData instanceof LinkableInterface) {
            return;
        }
        parent::mapDataToForms($viewData, $forms);
        /** @var Form[] $forms */
        $forms = iterator_to_array($forms);
        $data = [];
        foreach ($viewData->getLinks() as $link) {
            $data[] = [
                'url' => $link->getUrl(),
                'text' => $link->getText(),
            ];
        }
        $forms['links']->setData($data);
    }

    public function mapFormsToData($forms, &$viewData) : void {
        if ( ! $viewData instanceof LinkableInterface) {
            return;
        }
        $forms = iterator_to_array($forms);
        parent::mapFormsToData($forms, $viewData);
        if ( ! $this->em->contains($viewData)) {
            $this->em->persist($viewData);
            $this->em->flush();
        }

        foreach ($viewData->getLinks() as $link) {
            $this->em->remove($link);
            $viewData->removeLink($link);
        }
        foreach ($forms['links'] as $data) {
            $link = new Link();
            $link->setEntity($viewData);
            $link->setText($data['text']->getData());
            $link->setUrl($data['url']->getData());
            $this->em->persist($link);
        }
    }

    /**
     * @required
     */
    public function setEntityManager(EntityManagerInterface $em) : void {
        $this->em = $em;
    }

    /**
     * @required
     */
    public function setElementRepository(ElementRepository $repo) : void {
        $this->elementRepo = $repo;
    }
}
