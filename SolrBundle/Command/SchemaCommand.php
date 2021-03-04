<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Nines\SolrBundle\Mapper\EntityMapper;
use Nines\SolrBundle\Mapper\EntityMapperBuilder;
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

    /**
     * @var null|EntityMapper
     */
    private $mapper;

    protected static $defaultName = 'nines:solr:schema';

    public function __construct(EntityMapperBuilder $builder) {
        parent::__construct(self::$defaultName);
        $this->mapper = $builder->build();
    }

    protected function configure() : void {
        $this->setDescription('Show the solr schema.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        dump($this->mapper);

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
