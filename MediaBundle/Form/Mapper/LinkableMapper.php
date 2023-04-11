<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Form\Mapper;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Nines\MediaBundle\Entity\Link;
use Nines\MediaBundle\Entity\LinkableInterface;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;
use Symfony\Component\Form\Form;

class LinkableMapper extends PropertyPathMapper implements DataMapperInterface {
    private ?EntityManagerInterface $em = null;

    private bool $parentCall = true;

    public function setParentCall(bool $call) : void {
        $this->parentCall = $call;
    }

    /**
     * @param mixed $viewData
     * @param mixed $forms
     */
    public function mapDataToForms($viewData, $forms) : void {
        if ( ! $viewData instanceof LinkableInterface) {
            return;
        }
        if ($this->parentCall) {
            parent::mapDataToForms($viewData, $forms);
        }
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

    /**
     * @param mixed $viewData
     * @param mixed $forms
     *
     * @throws Exception
     */
    public function mapFormsToData($forms, &$viewData) : void {
        if ( ! $viewData instanceof LinkableInterface) {
            return;
        }
        if ($this->parentCall) {
            parent::mapFormsToData($forms, $viewData);
        }
        $forms = iterator_to_array($forms);
        if ( ! $this->em->contains($viewData)) {
            $this->em->persist($viewData);
            $this->em->flush();
        }

        foreach ($viewData->getLinks() as $link) {
            $this->em->remove($link);
            $viewData->removeLink($link);
        }
        foreach ($forms['links'] as $data) {
            if ( ! $data['url'] || ! $data['url']->getData()) {
                continue;
            }
            $link = new Link();
            $link->setEntity($viewData);
            $link->setText($data['text']->getData());
            $link->setUrl($data['url']->getData());
            $this->em->persist($link);
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
}
