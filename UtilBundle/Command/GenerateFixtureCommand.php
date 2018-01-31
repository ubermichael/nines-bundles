<?php

namespace Nines\UtilBundle\Command;

use Nines\UtilBundle\Generator\FixtureGenerator;
use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineCommand;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * GenerateFixtureCommand command.
 */
class GenerateFixtureCommand extends GenerateDoctrineCommand {

    /**
     * Configure the command.
     */
    protected function configure() {
        $this
                ->setName('generate:fixture')
                ->setDescription('Genrate a doctrine fixture.')
                ->setDefinition(array(
                    new InputOption('count', 'c', InputOption::VALUE_REQUIRED, 'Force overwrite', 1),
                    new InputOption('force', 'f', InputOption::VALUE_NONE, 'Force overwrite'),
                    new InputArgument('entity', InputArgument::REQUIRED, 'The entity class name to initialize (shortcut notation)'),
                ))
                ->setHelp(<<<EOT
The <info>%command.name%</info> command generates a fixture class based on a Doctrine entity.

<info>php %command.full_name% AcmeBlogBundle:Post</info>

Every generated file is based on a template. There are default templates but they can be overridden by placing custom templates in one of the following locations, by order of priority:

<info>BUNDLE_PATH/Resources/SensioGeneratorBundle/skeleton/fixture
APP_PATH/Resources/SensioGeneratorBundle/skeleton/fixture</info>
EOT
                )
        ;
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     *   Command input, as defined in the configure() method.
     * @param OutputInterface $output
     *   Output destination.
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $force = $input->hasOption('force');
        $count = $input->getOption('count');
        $entity = Validators::validateEntityName($input->getArgument('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        $entityClass = $this->getContainer()->get('doctrine')->getAliasNamespace($bundle) . '\\' . $entity;
        $metadata = $this->getEntityMetadata($entityClass);
        $bundle = $this->getApplication()->getKernel()->getBundle($bundle);
        $generator = $this->getGenerator($bundle);

        $generator->generate($bundle, $entity, $metadata[0], $count, $force);

        $output->writeln(sprintf('%s.php is at %s.', $generator->getClassName(), $generator->getClassPath()));
    }

    protected function createGenerator() {
        return new FixtureGenerator($this->getContainer()->get('filesystem'));
    }

}
