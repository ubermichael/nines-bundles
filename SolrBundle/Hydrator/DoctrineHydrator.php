<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Hydrator;

use Doctrine\ORM\EntityManagerInterface;
use Solarium\Core\Query\DocumentInterface;
use stdClass;

class DoctrineHydrator
{
    private EntityManagerInterface $em;

    /**
     * @param DocumentInterface|stdClass $document
     *
     * @return null|object
     */
    public function hydrate($document) {
        list($class, $id) = explode(':', $document->id);

        return $this->em->find($class, $id);
    }

    /**
     * @required
     */
    public function setEntityManager(EntityManagerInterface $em) : void {
        $this->em = $em;
    }
}
