<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Logging;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Solarium\Core\Query\AbstractQuery;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Query\Query as SelectQuery;
use Solarium\QueryType\Update\Query\Query as UpdateQuery;

/**
 * Solr Logger collects queries and log data for use in the Symfony toolbar and
 * profiler.
 */
class SolrLogger extends AbstractLogger {
    private bool $enabled = true;

    /**
     * Log messages.
     *
     * @var array<int,mixed>
     */
    private ?array $logs = null;

    /**
     * Count the log entries in various levels.
     *
     * @var array<string,int>
     */
    private ?array $counts = null;

    /**
     * @var Query[]
     */
    private ?array $queries = null;

    public function __construct() {
        $this->logs = [];
        $this->counts = [];
        $this->queries = [];
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * @param array<string,string> $context
     */
    private function interpolate(string $message, array $context = []) : string {
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
     * @param mixed $message
     * @param array<string,string> $context
     */
    public function log($level, $message, array $context = []) : void {
        if ( ! $this->enabled) {
            return;
        }
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
     * Add a query to the logs.
     *
     * @param SelectQuery|UpdateQuery $query
     */
    public function addQuery(AbstractQuery $query) : void {
        if ( ! $this->enabled) {
            return;
        }
        $this->queries[] = $query;
    }

    /**
     * Get the log messages.
     *
     * @return array<int,mixed>
     */
    public function getLogs() : array {
        return $this->logs;
    }

    /**
     * Get a count of the logs by level.
     *
     * @return array<string,int>
     */
    public function getCounts() : array {
        return $this->counts;
    }

    /**
     * Get the queries executed during the request.
     *
     * @return Query[]
     */
    public function getQueries() : array {
        return $this->queries;
    }

    /**
     * Enable or disable the logger. For bulk data loads this logger may consume
     * a lot of memory.
     */
    public function setEnabled(bool $enabled) : void {
        $this->enabled = $enabled;
    }
}
