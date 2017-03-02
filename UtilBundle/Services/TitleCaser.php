<?php

/*
 * Copyright (C) 2016 Michael Joyce <mjoyce@sfu.ca>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Nines\UtilBundle\Services;

use Monolog\Logger;

/**
 * Apply title casing to a string.
 *
 * @author Michael Joyce <mjoyce@sfu.ca>
 */
class TitleCaser {

    /**
     * Monolog logger.
     * 
     * @var Logger
     */
    private $logger;

    /**
     * Set the service's logger.
     * 
     * @param Logger $logger
     */
    public function setLogger(Logger $logger) {
        $this->logger = $logger;
    }

    /**
     * Words which should be lower case.
     *
     * @var array
     */
    private $lower = array(
        'and', 'at', 'a', 'an', 'are', 'in', 'or', 'of', 'on', 'to', 'for',
        'was', 'with', 'by', 'from', 'which', 'the',
    );

    /**
     * Abbreviations which should always be upper case.
     * @var array
     */
    private $states = array(
        'AB', 'BC', 'MB', 'NB', 'NL', 'NS', 'NT', 'NU', 'ON', 'PE', 'PEI',
        'QC', 'SK', 'YT',
        'US', 'USA',
        'AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'DC', 'FL', 'GA', 'HI',
        'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD', 'MA', 'MI', 'MN',
        'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ', 'NM', 'NY', 'NC', 'ND', 'OH',
        'OK', 'OR', 'PA', 'RI', 'SC', 'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA',
        'WV', 'WI', 'WY',
    );
    
    /**
     * Ordinal suffixes.
     *
     * @var array
     */
    private $ordinals = array(
        'st', 'nd', 'rd', 'th',
    );
    
    /**
     * Abbreviations which get special casing.
     *
     * @var string
     */
    private $exceptions = array(
        'phd' => 'PhD',
        'cihm' => 'CIHM',
        'ichm' => 'ICHM',
        'ubc' => 'UBC',
        's\.n\.' => 'S.N.',
    );

    /**
     * Mangle the short words to lower case.
     * 
     * @param string $string
     * @return string
     */
    public function shortWords($string) {
        $match = implode('|', $this->lower);
        return preg_replace_callback("/\s(?:$match)\b/ui", function(array $m) {
            return mb_convert_case($m[0], MB_CASE_LOWER);
        }, $string);
    }

    /**
     * Mangle the punctuation and make it all title casey.
     * 
     * @param string $string
     * @return string
     */
    public function punctuation($string) {
        $match = implode('|', $this->lower);

        // spaces before punctuation
        $s = preg_replace('/\s*([[:punct:]])/u', '$1', $string);

        // punctuation stopword
        $s = preg_replace_callback("/([[:punct:]])(\s+)($match)/iu", function(array $m) {
            return $m[1] . ' ' . mb_convert_case($m[3], MB_CASE_TITLE);
        }, $s);
        return $s;
    }

    /**
     * Make the state abbreviations upper case.
     * 
     * @param string $string
     * @return string
     */
    public function states($string) {
        $match = implode('|', $this->states);
        $s = preg_replace_callback("/\b($match)\b/iu", function(array $m) {
            return mb_convert_case($m[1], MB_CASE_UPPER);
        }, $string);
        return $s;
    }

    /**
     * Attempt to get the surnames right. 
     * 
     * @param string $string
     * @return string
     */
    public function names($string) {
        $s = preg_replace_callback("/\b(Mc|Mac|O'|D')([a-z])/iu", function(array $m) {
            return $m[1] . mb_convert_case($m[2], MB_CASE_UPPER);
        }, $string);
        return $s;
    }

    /**
     * Mangle Roman numerials.
     * 
     * @param string $string
     * @return string
     */
    public function roman($string) {
        $s = preg_replace_callback('/\b([ivxlcdm]+)\b/iu', function(array $m) {
            return mb_convert_case($m[1], MB_CASE_UPPER);
        }, $string);
        return $s;
    }

    /**
     * Set the ordinals in a title to the correct case.
     * 
     * @param string $string
     * @return string
     */
    public function ordinals($string) {
        $match = implode('|', $this->ordinals);
        $s = preg_replace_callback("/\b([0-9]+)($match)\b/iu", function(array $m) {
            return $m[1] . mb_convert_case($m[2], MB_CASE_LOWER);
        }, $string);
        return $s;
    }

    /**
     * Handle the exceptions.
     * 
     * @param string $string
     * @return string
     */
    public function exceptions($string) {
        $match = implode('|', array_keys($this->exceptions));
        $s = preg_replace_callback("/\b($match)\b/iu", function(array $m) {
            return $this->exceptions[mb_convert_case($m[1], MB_CASE_LOWER)];
        }, $string);
        return $s;
    }

    /**
     * Returns the titlecased version of string.
     * 
     * @param string $string
     */
    public function titlecase($string) {
        $s = mb_convert_case($string, MB_CASE_TITLE);
        $s = $this->shortWords($s);
        $s = $this->punctuation($s);
        $s = $this->states($s);
        $s = $this->names($s);
        $s = $this->roman($s);
        $s = $this->ordinals($s);
        $s = $this->exceptions($s);
        return $s;
    }

}
