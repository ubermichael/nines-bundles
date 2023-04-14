<?php

declare(strict_types=1);

namespace Nines\MakerBundle\Service;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Doctrine\Inflector\Language;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Str;

class Metadata {
    protected FileManager $fileManager;

    protected DoctrineHelper $doctrineHelper;

    private LoggerInterface $logger;

    private Generator $generator;

    private Inflector $inflector;

    public function __construct(LoggerInterface $logger, DoctrineHelper $doctrineHelper, FileManager $fileManager, Generator $generator) {
        $this->logger = $logger;
        $this->doctrineHelper = $doctrineHelper;
        $this->fileManager = $fileManager;
        $this->generator = $generator;
        $this->inflector = InflectorFactory::createForLanguage(Language::ENGLISH)->build();
    }

    public function getDetails(string $name) : array {
        $name = preg_replace('!^\\\\?App\\\\Entity\\\\!', '', $name);
        $entityClassDetails = $this->generator->createClassNameDetails($name, 'Entity\\');
        $relativeName = $entityClassDetails->getRelativeName();
        $inBundle = ($name !== $relativeName);
        $nsPrefix = mb_strstr($name, $relativeName, true);

        $entityTestDetails = $this->generator->createClassNameDetails(
            $nsPrefix . 'Test\\Entity\\' . $entityClassDetails->getShortName() . 'Test',
            '',
            'Test',
        );

        $controllerClassDetails = $this->generator->createClassNameDetails(
            $nsPrefix . 'Controller\\' . $entityClassDetails->getShortName() . 'Controller',
            '',
            'Controller',
        );

        $controllerTestDetails = $this->generator->createClassNameDetails(
            $nsPrefix . 'Test\\Controller\\' . $entityClassDetails->getShortName() . 'ControllerTest',
            '',
            'ControllerTest',
        );

        $formClassDetails = $this->generator->createClassNameDetails(
            $nsPrefix . 'Form\\' . $entityClassDetails->getShortName() . 'Type',
            '',
            '',
        );

        $fixtureClassDetails = $this->generator->createClassNameDetails(
            $nsPrefix . 'DataFixtures\\' . $entityClassDetails->getShortName() . 'Fixtures',
            '',
            '',
        );

        $repositoryClassDetails = $this->generator->createClassNameDetails(
            $nsPrefix . 'Repository\\' . $entityClassDetails->getShortName() . 'Repository',
            '',
            'Repository',
        );

        $repositoryTestDetails = $this->generator->createClassNameDetails(
            $nsPrefix . 'Test\\Repository\\' . $entityClassDetails->getShortName() . 'RepositoryTest',
            '',
            'RepositoryTest',
        );

        $doctrineDetails = $this->doctrineHelper->createDoctrineDetails($entityClassDetails->getFullName());
        $meta = $this->doctrineHelper->getMetadata($entityClassDetails->getFullName());
        $associationFields = [];
        if ( ! is_array($meta)) {
            $associationFields = $this->doctrineHelper->getMetadata($entityClassDetails->getFullName())->associationMappings;

            foreach ($associationFields as $key => $field) {
                $details = $this->generator->createClassNameDetails($field['targetEntity'], '');
                $prefix = preg_replace('/(?:^app_|bundle_)entity_/', '', Str::asRouteName($details->getRelativeNameWithoutSuffix()));
                $associationFields[$key]['route_name_prefix'] = $prefix;
            }
        }

        return [
            'entity_class_name' => $entityClassDetails->getFullName(),
            'entity_class_shortName' => $entityClassDetails->getShortName(),
            'entity_class_path' => $this->fileManager->getRelativePathForFutureClass($entityClassDetails->getFullName()),
            'entity_class_ns' => Str::removeSuffix($entityClassDetails->getFullName(), '\\' . $entityClassDetails->getShortName()),
            'entity_var_single' => lcfirst($this->inflector->singularize($entityClassDetails->getShortName())),
            'entity_var_plural' => lcfirst($this->inflector->pluralize($entityClassDetails->getShortName())),

            'entity_fields' => $doctrineDetails ? $doctrineDetails->getDisplayFields() : [],
            'association_fields' => $associationFields,

            'entity_test_name' => $entityTestDetails->getFullName(),
            'entity_test_shortName' => $entityTestDetails->getShortName(),
            'entity_test_path' => $this->fileManager->getRelativePathForFutureClass($entityTestDetails->getFullName()),
            'entity_test_ns' => Str::removeSuffix($entityTestDetails->getFullName(), '\\' . $entityTestDetails->getShortName()),

            'route_name_prefix' => preg_replace('/(?:^app_|bundle_entity_)/', '', Str::asRouteName($entityClassDetails->getRelativeNameWithoutSuffix())),
            'route_path_prefix' => preg_replace('!^/\w+(/.*)/bundle/entity/!', '$1/', Str::asRoutePath($entityClassDetails->getRelativeNameWithoutSuffix())),

            'controller_class_name' => $controllerClassDetails->getFullName(),
            'controller_class_shortName' => $controllerClassDetails->getShortName(),
            'controller_class_path' => $this->fileManager->getRelativePathForFutureClass($controllerClassDetails->getFullName()),
            'controller_class_ns' => Str::removeSuffix($controllerClassDetails->getFullName(), '\\' . $controllerClassDetails->getShortName()),

            'controller_test_name' => $controllerTestDetails->getFullName(),
            'controller_test_shortName' => $controllerTestDetails->getShortName(),
            'controller_test_path' => $this->fileManager->getRelativePathForFutureClass($controllerTestDetails->getFullName()),
            'controller_test_ns' => Str::removeSuffix($controllerTestDetails->getFullName(), '\\' . $controllerTestDetails->getShortName()),

            'form_class_name' => $formClassDetails->getFullName(),
            'form_class_shortName' => $formClassDetails->getShortName(),
            'form_class_path' => $this->fileManager->getRelativePathForFutureClass($formClassDetails->getFullName()),
            'form_class_ns' => Str::removeSuffix($formClassDetails->getFullName(), '\\' . $formClassDetails->getShortName()),

            'fixture_class_name' => $fixtureClassDetails->getFullName(),
            'fixture_class_shortName' => $fixtureClassDetails->getShortName(),
            'fixture_class_path' => $this->fileManager->getRelativePathForFutureClass($fixtureClassDetails->getFullName()),
            'fixture_class_ns' => Str::removeSuffix($fixtureClassDetails->getFullName(), '\\' . $fixtureClassDetails->getShortName()),

            'repository_class_name' => $repositoryClassDetails->getFullName(),
            'repository_class_shortName' => $repositoryClassDetails->getShortName(),
            'repository_class_path' => $this->fileManager->getRelativePathForFutureClass($repositoryClassDetails->getFullName()),
            'repository_class_ns' => Str::removeSuffix($repositoryClassDetails->getFullName(), '\\' . $repositoryClassDetails->getShortName()),

            'repository_test_name' => $repositoryTestDetails->getFullName(),
            'repository_test_shortName' => $repositoryTestDetails->getShortName(),
            'repository_test_path' => $this->fileManager->getRelativePathForFutureClass($repositoryTestDetails->getFullName()),
            'repository_test_ns' => Str::removeSuffix($repositoryTestDetails->getFullName(), '\\' . $repositoryTestDetails->getShortName()),
            'repository_var_single' => lcfirst($this->inflector->singularize($entityClassDetails->getShortName())) . 'Repo',

            'twig_path_prefix' => ($inBundle ? '@' . preg_replace('/^(\w+)\\\\(\w+Bundle).*/', '$1$2', $entityClassDetails->getFullName()) . '/' : '') . Str::asTwigVariable($entityClassDetails->getShortName()),
            'twig_var_single' => Str::asTwigVariable($entityClassDetails->getShortName()),
            'twig_var_plural' => $this->inflector->pluralize(Str::asTwigVariable($entityClassDetails->getShortName())),
        ];
    }
}
