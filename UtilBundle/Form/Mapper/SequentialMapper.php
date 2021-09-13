<?php

namespace Nines\UtilBundle\Form\Mapper;

use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception;
use Symfony\Component\Form\FormInterface;

class SequentialMapper implements DataMapperInterface {

    /**
     * @var DataMapperInterface[]
     */
    private array $mappers;

    public function __construct(DataMapperInterface ...$mappers) {
        $this->mappers = $mappers;
    }

    public function mapDataToForms($viewData, $forms) {
        foreach($this->mappers as $mapper) {
            $mapper->mapDataToForms($viewData, $forms);
        }
    }

    public function mapFormsToData($forms, &$viewData) {
        foreach($this->mappers as $mapper) {
            $mapper->mapFormsToData($forms, $viewData);
        }
    }
}
