<?php


namespace Nines\UtilBundle\Services;


use Doctrine\Common\Util\ClassUtils;
use Exception;
use Nines\UtilBundle\Entity\AbstractEntity;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EntityLinker {

    /**
     * Map of FQCN to the show route name.
     * @var array
     */
    private $routing;
    /**
     * @var UrlGeneratorInterface
     */
    private UrlGeneratorInterface $urlGenerator;

    public function __construct($routing = []) {
        $this->routing = $routing;
    }

    /**
     * @param $entity
     * @param array $parameters
     * @param int $type
     *
     * @return string
     * @throws Exception
     */
    public function link($entity, $parameters = [], $type = UrlGeneratorInterface::ABSOLUTE_PATH) {
        $class = ClassUtils::getClass($entity);
        if( ! isset($this->routing[$class])) {
            throw new Exception("Cannot link to unconfigured entity {$class}.");
        }
        $name = $this->routing[$class];
        $params = array_merge(['id' => $entity->getId()], $parameters);
        return $this->urlGenerator->generate($name, $params, $type);
    }

    public function setRouting($routing = []) {
        $this->routing = $routing;
    }

    /**
     * @param UrlGeneratorInterface $urlGenerator
     * @required
     */
    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator) {
        $this->urlGenerator = $urlGenerator;
    }

}
