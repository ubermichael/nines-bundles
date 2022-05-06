<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Form\Mapper;

use Symfony\Component\Form\DataMapperInterface;

class SequentialMapper implements DataMapperInterface {
    /**
     * @var DataMapperInterface[]
     */
    private ?array $mappers = null;

    public function __construct(DataMapperInterface ...$mappers) {
        $this->mappers = $mappers;
    }

    public function mapDataToForms($viewData, $forms) : void {
        foreach ($this->mappers as $mapper) {
            $mapper->mapDataToForms($viewData, $forms);
        }
    }

    public function mapFormsToData($forms, &$viewData) : void {
        foreach ($this->mappers as $mapper) {
            $mapper->mapFormsToData($forms, $viewData);
        }
    }
}
