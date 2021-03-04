<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Mapper;

use Doctrine\ORM\EntityManagerInterface;

class EntityMapperBuilder {
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var array
     */
    private $copyFields;

    private static $entityMapper;

    private static function createMapping() {
        // do stuff.
        return new EntityMapper();
    }

    public function build() {
        if ( ! self::$entityMapper) {
            self::$entityMapper = $this->createMapping();
        }

        return self::$entityMapper;
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
