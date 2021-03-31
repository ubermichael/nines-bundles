<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Logging;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Solarium\QueryType\Select\Query\Query;

/**
 * Solr Logger collects queries and log data for use in the Symfony toolbar and
 * profiler.
 */
class SolrLogger extends AbstractLogger
{
    /**
     * @var bool
     */
    private $enabled = true;

    /**
     * Log messages.
     *
     * @var array
     */
    private $logs;

    /**
     * Count the log entries in various levels.
     *
     * @var array
     */
    private $counts;

    /**
     * @var Query[]
     */
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

    /**
     * PSR logging implementation.
     *
     * @see LogLevel
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = []) : void {
        $bt = debug_backtrace();
        $caller = $bt[1];

        $this->logs[] = [
            $level,
            $this->interpolate($message, $context),
            $caller['file'],
            $caller['line'],
        ];
        if ( ! isset($this->counts[$level])) {
            $this->counts[$level] = 1;
        } else {
            $this->counts[$level]++;
        }
    }

    /**
     * Add a query to the logs
     *
     * @param $query
     */
    public function addQuery($query) : void {
        $this->queries[] = $query;
    }

    /**
     * Get the log messages
     *
     * @return array
     */
    public function getLogs() {
        return $this->logs;
    }

    /**
     * Get a count of the logs by level
     *
     * @return array
     */
    public function getCounts() {
        return $this->counts;
    }

    /**
     * Get the queries executed during the request
     *
     * @return Query[]
     */
    public function getQueries() {
        return $this->queries;
    }

    /**
     * Enable or disable the logger. For bulk data loads this logger may consume
     * a lot of memory.
     *
     * @param $enabled
     */
    public function setEnabled($enabled) : void {
        $this->enabled = $enabled;
    }
}
