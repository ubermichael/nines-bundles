<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

trait ContributorTrait {
    /**
     * @var array
     * @ORM\Column(type="array", nullable=false)
     */
    private $contributions;

    public function __construct() {
        $this->contributions = [];
    }

    public function getContributions() : ?array {
        if( ! $this->contributions) {
            return [];
        }
        usort($this->contributions, function ($a, $b) {
            $d = $b['date'] <=> $a['date'];
            if ($d) {
                return $d;
            }

            return $a['name'] <=> $b['name'];
        });

        return $this->contributions;
    }

    public function setContributions(array $contributions) {
        $this->contributions = $contributions;

        return $this;
    }

    public function addContribution(DateTimeImmutable $date, $name) {
        if( ! $this->contributions) {
            $this->contributions = [];
        }
        $str = $date->format('Y-m-d');

        foreach ($this->contributions as $contribution) {
            if ($contribution['date'] === $str && $contribution['name'] === $name) {
                return $this;
            }
        }
        $this->contributions[] = ['date' => $str, 'name' => $name];

        return $this;
    }
}
