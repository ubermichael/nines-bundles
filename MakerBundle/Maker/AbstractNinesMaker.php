<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MakerBundle\Maker;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Nines\UtilBundle\Entity\AbstractEntity;
use ReflectionClass;
use ReflectionException;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\MakerInterface;
use Symfony\Bundle\MakerBundle\Renderer\FormTypeRenderer;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\ClassSourceManipulator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Twig\Environment;

abstract class AbstractNinesMaker implements MakerInterface {
    protected const GENERATED = ['id', 'created', 'updated'];

    protected ?DoctrineHelper $doctrineHelper = null;

    protected ?FormTypeRenderer $formTypeRenderer = null;

    protected ?Environment $twig = null;

    protected ?int $fixtureCount = null;

    private ?Inflector $inflector = null;

    public function __construct() {
        $this->inflector = InflectorFactory::create()->build();
    }

    protected function pluralize(string $word) : string {
        return $this->inflector->pluralize($word);
    }

    protected function singularize(string $word) : string {
        return $this->inflector->singularize($word);
    }

    /**
     * @throws ReflectionException
     *
     * @return int[]|string[]
     *
     * @phpstan-param ClassMetadataInfo<AbstractEntity> $classMetadata
     */
    protected function getMappedFieldsInEntity(ClassMetadataInfo $classMetadata) : array {
        $classReflection = $classMetadata->reflClass;

        $targetFields = array_merge(
            array_keys($classMetadata->fieldMappings),
            array_keys($classMetadata->associationMappings),
        );

        if ($classReflection) {
            // exclude traits
            $traitProperties = [];

            foreach ($classReflection->getTraits() as $trait) {
                foreach ($trait->getProperties() as $property) {
                    $traitProperties[] = $property->getName();
                }
            }

            $targetFields = array_diff($targetFields, $traitProperties);

            // exclude inherited properties
            $targetFields = array_filter($targetFields, function($field) use ($classReflection) {
                return $classReflection->hasProperty($field)
                    && $classReflection->getProperty($field)->getDeclaringClass()->getName() === $classReflection->getName();
            });
        }

        return $targetFields;
    }

    /**
     * @throws ReflectionException
     */
    protected function getPathOfClass(string $class) : string {
        return (new ReflectionClass($class))->getFileName();
    }

    protected function createClassManipulator(string $classPath, ?bool $overwrite = true, ?bool $annotations = false) : ClassSourceManipulator {
        $source = file_get_contents($classPath);

        return new ClassSourceManipulator($source, $overwrite, $annotations);
    }

    /**
     * @throws ReflectionException
     */
    protected function shortName(string $fqcn) : string {
        $reflect = new ReflectionClass($fqcn);

        return $reflect->getShortName();
    }

    /**
     * @throws ReflectionException
     *
     * @return array<string,mixed>
     */
    protected function collect(Generator $generator, string $name, ?bool $shallow = false) : array {
        // todo: figure out what to do with FQCNs here.
        $entityClassDetails = $generator->createClassNameDetails($name, 'Entity\\');
        $repositoryClassDetails = $generator->createClassNameDetails($entityClassDetails->getShortName() . 'Repository', 'Repository\\', 'Repository');
        $entityVarPlural = lcfirst($this->pluralize($entityClassDetails->getShortName()));
        $entityVarSingular = lcfirst($this->singularize($entityClassDetails->getShortName()));

        $entityTwigVarPlural = Str::asTwigVariable($entityVarPlural);
        $entityTwigVarSingular = Str::asTwigVariable($entityVarSingular);

        $controllerClassDetails = $generator->createClassNameDetails(
            $entityClassDetails->getRelativeNameWithoutSuffix() . 'Controller',
            'Controller\\',
            'Controller',
        );

        $indexClassDetails = $generator->createClassNameDetails(
            $entityClassDetails->getRelativeNameWithoutSuffix() . 'Index',
            'Index\\',
            'Index',
        );

        $formClassDetails = $generator->createClassNameDetails(
            $entityClassDetails->getRelativeNameWithoutSuffix() . 'Type',
            'Form\\',
            'Type',
        );

        $fixtureClassDetails = $generator->createClassNameDetails(
            $entityClassDetails->getRelativeNameWithoutSuffix() . 'Fixtures',
            'DataFixtures\\',
            'Fixtures',
        );

        $testClassDetails = $generator->createClassNameDetails(
            $entityClassDetails->getRelativeNameWithoutSuffix() . 'Test',
            'Tests\\',
            'Test',
        );

        $routeName = preg_replace('/^app_/', '', Str::asRouteName($controllerClassDetails->getRelativeNameWithoutSuffix()));
        $templatesPath = Str::asFilePath($controllerClassDetails->getRelativeNameWithoutSuffix());

        $classMetadata = $this->doctrineHelper->getMetadata($entityClassDetails->getFullName());
        if (is_array($classMetadata)) {
            $mappedFieldNames = [];
        } else {
            $mappedFieldNames = $this->getMappedFieldsInEntity($classMetadata);
        }

        $relations = [];
        if ($classMetadata && ! $shallow) {
            foreach ($classMetadata->associationMappings as $name => $association) {
                $relations[$name] = $this->collect($generator, $this->shortName($association['targetEntity']), true);
            }
        }

        $indexes = [];
        if ($classMetadata && isset($classMetadata->table['indexes'])) {
            $indexes = $classMetadata->table['indexes'];
        }

        $mappings = $classMetadata ? $classMetadata->fieldMappings : [];
        $mappedFieldNames = array_filter(array_keys($mappings), fn($item) => ! in_array($item, self::GENERATED, true));

        return [
            'generated' => self::GENERATED,
            'namespace' => 'App',

            'entity_class_name' => $entityClassDetails->getShortName(),
            'entity_full_class_name' => $entityClassDetails->getFullName(),

            'entity_twig_var_plural' => $entityTwigVarPlural,
            'entity_twig_var_singular' => $entityTwigVarSingular,
            'entity_var_plural' => $entityVarPlural,
            'entity_var_singular' => $entityVarSingular,

            'mapped_field_names' => $mappedFieldNames,
            'field_mappings' => $mappings,
            'associations' => $classMetadata ? $classMetadata->associationMappings : [],
            'relations' => $relations,
            'indexes' => $indexes,

            'index_class_name' => $indexClassDetails->getShortName(),
            'index_full_class_name' => $indexClassDetails->getFullName(),
            'index_var' => lcfirst($this->singularize($indexClassDetails->getShortName())),

            'form_class_name' => $formClassDetails->getShortName(),
            'form_full_class_name' => $formClassDetails->getFullName(),

            'controller_class_name' => $controllerClassDetails->getShortName(),
            'controller_full_class_name' => $controllerClassDetails->getFullName(),

            'repository_class_name' => $repositoryClassDetails->getShortName(),
            'repository_full_class_name' => $repositoryClassDetails->getFullName(),
            'repository_var' => lcfirst($this->singularize($repositoryClassDetails->getShortName())),

            'fixture_class_name' => $fixtureClassDetails->getShortName(),
            'fixture_full_class_name' => $fixtureClassDetails->getFullName(),

            'test_class_name' => $testClassDetails->getShortName(),
            'test_full_class_name' => $testClassDetails->getFullName(),

            'route_name' => $routeName,
            'route_path' => Str::asRoutePath($controllerClassDetails->getRelativeNameWithoutSuffix()),
            'templates_path' => $templatesPath,
        ];
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setTwig(Environment $environment) : void {
        $this->twig = $environment;
    }

    public function setDoctrineHelper(DoctrineHelper $doctrineHelper) : void {
        $this->doctrineHelper = $doctrineHelper;
    }

    public function setFormTypeRenderer(FormTypeRenderer $formTypeRenderer) : void {
        $this->formTypeRenderer = $formTypeRenderer;
    }

    /**
     * {@inheritdoc}
     */
    public function configureDependencies(DependencyBuilder $dependencies) : void {
    }

    public function setFixtureCount(int $fixtureCount) : void {
        $this->fixtureCount = $fixtureCount;
    }

    /**
     * {@inheritdoc}
     */
    public function interact(InputInterface $input, ConsoleStyle $io, Command $command) : void {
        // TODO: Implement interact() method.
    }
}
