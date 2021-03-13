<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Metadata;

use Nines\UtilBundle\Entity\AbstractEntity;

abstract class Metadata {
    abstract public function fetch(AbstractEntity $entity);

    /**
     * @param $string
     */
    protected function parseFunctionCall(string $string) : array {
        $name = $string;
        $args = [];

        if (false !== ($n = mb_strpos($string, '('))) {
            $name = mb_substr($string, 0, $n);
            $args = explode(',', mb_substr($string, $n + 1, -1));
            $args = array_map(function ($s) {return preg_replace("/^(?:[[:space:]'\"]*)|(?:[[:space:]'\"]*)$/u", '', $s); }, $args);
        }

        return [$name, $args];
    }
}
