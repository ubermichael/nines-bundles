<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nines\UtilBundle\Generator;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use RuntimeException;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * Description of FixtureGenerator
 */
class FixtureGenerator extends Generator {
    private $filesystem;
    private $className;
    private $classPath;

    /**
     * Constructor.
     *
     * @param Filesystem $filesystem A Filesystem instance
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function getClassPath()
    {
        return $this->classPath;
    }
    
    public function generate(BundleInterface $bundle, $entity, ClassMetadataInfo $metadata, $count = 1, $forceOverwrite = false) {
        $parts = explode('\\', $entity);
        $entityClass = array_pop($parts);

        $this->className = 'Load'.$entityClass;
        $dirPath = $bundle->getPath().'/DataFixtures/ORM';
        $this->classPath = $dirPath.'/Load'.str_replace('\\', '/', $entity).'.php';

        if (!$forceOverwrite && file_exists($this->classPath)) {
            throw new RuntimeException(sprintf('Unable to generate the %s form class as it already exists under the %s file', $this->className, $this->classPath));
        }

        $parts = explode('\\', $entity);
        array_pop($parts);

        $this->renderFile('fixture/LoadFixture.php.twig', $this->classPath, array(
            'count' => $count,
            'field_metadata' => $metadata->fieldMappings,
            'fields' => $this->getFieldsFromMetadata($metadata),
            'namespace' => $bundle->getNamespace(),
            'entity_namespace' => implode('\\', $parts),
            'entity_class' => $entityClass,
            'bundle' => $bundle->getName(),
            'fixture_class' => $this->className,
        ));
    }
    
    /**
     * Returns an array of fields. Fields can be both column fields and
     * association fields.
     *
     * @param ClassMetadataInfo $metadata
     *
     * @return array $fields
     */
    private function getFieldsFromMetadata(ClassMetadataInfo $metadata)
    {
        $fields = (array) $metadata->fieldNames;

        // Remove the primary key field if it's not managed manually
        if (!$metadata->isIdentifierNatural()) {
            $fields = array_diff($fields, $metadata->identifier);
        }

        foreach ($metadata->associationMappings as $fieldName => $relation) {
            if ($relation['type'] !== ClassMetadataInfo::ONE_TO_MANY) {
                $fields[] = $fieldName;
            }
        }

        return $fields;
    }
}
