<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Command;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Nines\SolrBundle\Annotation\Document;
use Nines\SolrBundle\Annotation\Field;
use Nines\SolrBundle\Annotation\Id;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchemaCommand extends Command {
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var array
     */
    private $copyFields;

    protected static $defaultName = 'nines:solr:schema';

    protected function configure() : void {
        $this->setDescription('Show the solr schema.');
    }

    /**
     * @param ReflectionProperty[] $properties
     *
     * @throws Exception
     */
    protected function getIdentifier($properties) {
        $identifier = null;
        $reader = new AnnotationReader();

        foreach ($properties as $property) {
            $annotation = $reader->getPropertyAnnotation($property, Id::class);
            if ( ! $annotation) {
                continue;
            }
            if ($identifier) {
                throw new Exception('Cannot have two identifiers in ' . $property->getDeclaringClass()->getName());
            }
            $identifier = $property;
        }

        return $identifier;
    }

    protected function getProperties(ReflectionClass $rc) {
        $properties = [];
        do {
            foreach ($rc->getProperties() as $property) {
                $properties[$property->getName()] = $property;
            }
        } while ($rc = $rc->getParentClass());

        return $properties;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        foreach ($this->copyFields as $field) {
            $output->writeln('copyField: ' . $field['from'] . '->' . $field['to']);
        }
        AnnotationRegistry::registerLoader('class_exists');
        $reader = new AnnotationReader();
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();

        foreach ($metadata as $meta) {
            $reflection = $meta->getReflectionClass();
            $classAnnotation = $reader->getClassAnnotation($reflection, Document::class);
            if ( ! $classAnnotation) {
                continue;
            }
            $output->writeln($meta->getName());
            $properties = $this->getProperties($reflection);
            $identifier = $this->getIdentifier($properties);
            if ($identifier) {
                $output->writeln('  class_name: ' . $meta->getName() . ' (string)');
                $output->writeln('  identifier: ' . $meta->getName() . ':{' . $identifier->getName() . '}' . ' (string)');
            }

            foreach ($properties as $property) {
                $propAnnotation = $reader->getPropertyAnnotation($property, Field::class);
                if ( ! $propAnnotation) {
                    continue;
                }
                if ( ! $propAnnotation->name) {
                    $propAnnotation->name = mb_strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $property->getName()));
                }
                $propAnnotation->name .= Field::TYPE_MAP[$propAnnotation->type];
                $output->writeln('  ' . $property->getName() . ' -> ' . $propAnnotation->name . ' (' . $propAnnotation->type . ')');
            }
            $output->writeln('');
        }

        return 0;
    }

    /**
     * @required
     */
    public function setEntityManager(EntityManagerInterface $em) : void {
        $this->em = $em;
    }

    public function setCopyFields($copyFields) : void {
        $this->copyFields = $copyFields;
    }
}
