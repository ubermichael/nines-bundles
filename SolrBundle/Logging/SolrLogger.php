<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Logging;

use Psr\Log\AbstractLogger;

class SolrLogger extends AbstractLogger
{
    /**
     * @var bool
     */
    private $enabled = true;

    private $logs;

    private $counts;

    private $queries;

    public function __construct() {
        $this->logs = [];
        $this->counts = [];
        $this->queries = [];
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * @param string $message
     */
    private function interpolate($message, array $context = []) : string {
        // build a replacement array with braces around the context keys
        $replace = [];

        foreach ($context as $key => $val) {
            // check that the value can be cast to string
            if ( ! is_array($val) && ( ! is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }

    public function log($level, $message, array $context = []) : void {
        $bt = debug_backtrace();
        $caller=$bt[1];

        $this->logs[] = [
            $level,
            $this->interpolate($message, $context),
            $caller['file'],
            $caller['line']
        ];
        if( ! isset($this->counts[$level])) {
            $this->counts[$level] = 1;
        } else {
            $this->counts[$level]++;
        }
    }

    public function addQuery($query) {
        $this->queries[] = $query;
    }

    public function getLogs() {
        return $this->logs;
    }

    public function getCounts() {
        return $this->counts;
    }

    public function getQueries() {
        return $this->queries;
    }

    public function setEnabled($enabled) : void {
        $this->enabled = $enabled;
    }
}
