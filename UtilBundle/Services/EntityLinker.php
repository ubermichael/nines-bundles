<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Services;

use Doctrine\Common\Util\ClassUtils;
use Exception;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EntityLinker {
    /**
     * Map of FQCN to the show route name.
     *
     * @var array
     */
    private $routing;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct($routing = []) {
        $this->routing = $routing;
    }

    /**
     * @param $entity
     * @param array $parameters
     * @param int $type
     *
     * @throws Exception
     *
     * @return string
     */
    public function link($entity, $parameters = [], $type = UrlGeneratorInterface::ABSOLUTE_PATH) {
        $class = ClassUtils::getClass($entity);
        if ( ! isset($this->routing[$class])) {
            throw new Exception("Cannot link to unconfigured entity {$class}.");
        }
        $name = $this->routing[$class];
        $params = array_merge(['id' => $entity->getId()], $parameters);

        return $this->urlGenerator->generate($name, $params, $type);
    }

    public function setRouting($routing = []) : void {
        $this->routing = $routing;
    }

    /**
     * @required
     */
    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator) : void {
        $this->urlGenerator = $urlGenerator;
    }
}
