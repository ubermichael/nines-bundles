<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Services;

use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * Various text mangling functions for twig and for other symfony stuff.
 */
class Text {
    /**
     * Monolog logger.
     *
     * @var Logger
     */
    private $logger;

    private $defaultTrimLenth;

    public function __construct($defaultTrimLength, LoggerInterface $logger) {
        $this->defaultTrimLenth = $defaultTrimLength;
        $this->logger = $logger;
    }

    /**
     * Build a plain, searchable version of a marked up text.
     *
     * @param mixed $content
     *
     * @return null|string|string[]
     */
    public function plain($content) {
        $plain = strip_tags($content);
        $converted = html_entity_decode($plain, ENT_HTML5, 'UTF-8');
        $trimmed = preg_replace('/(^\\s+)|(\\s+$)/u', '', $converted);
        // \xA0 is the result of converting nbsp.
        return preg_replace('/[[:space:]\\x{A0}]/su', ' ', $trimmed);
    }

    /**
     * Find the keyword in the plain text and highlight it. Returns a list
     * of the higlights as KWIC results.
     *
     * @param string $content
     * @param string $keyword
     *
     * @return array
     */
    public function searchHighlight($content, $keyword) {
        $text = $this->plain($content);
        $i = stripos($text, $keyword);
        $regex = preg_quote($keyword);
        $results = [];
        while (false !== $i) {
            $s = substr($text, max([0, $i - 60]), 120);
            $results[] = preg_replace("/({$regex})/i", '<mark>$1</mark>', $s);
            $i = stripos($text, $keyword, $i + 1);
        }

        return array_unique($results);
    }

    /**
     * Create a URL-friendly slug from a string.
     *
     * Drops leading/trailing spaces, transliterates digraphs, lowercases,
     * and replaces non letter/digit characters to the separator. Periods at
     * the end of the string are removed.
     *
     * @param string $string
     * @param string $separator
     *
     * @return string
     */
    public function slug($string, $separator = '-') {
        if (null === $string) {
            return;
        }

        // trim spaces and periods.
        $s = preg_replace('/(^[\s.]*)|([\s.]*$)/u', '', $string);

        // transliterate digraphs and accents
        $s = iconv('utf-8', 'us-ascii//TRANSLIT', $s);

        // lowercase
        $s = mb_convert_case($s, MB_CASE_LOWER, 'UTF-8');

        // strip non letter/digit/space chars
        $s = preg_replace('/[^a-z0-9 _.-]/u', '', $s);

        // transform spaces and runs of separators to separator.
        $quoted = preg_quote($separator ?? '', '/');

        return preg_replace("/(\\s|{$quoted})+/u", $separator, $s);
    }

    /**
     * Strip tags from HTML and then trim it to a number of words.
     *
     * @param string $string
     * @param string $length
     * @param string $suffix
     *
     * @return string
     */
    public function trim($string, $length = null, $suffix = '...') {
        if (null === $length) {
            $length = $this->defaultTrimLenth;
        }
        $plain = $this->plain($string);
        $words = preg_split('/\s+/u', $plain, $length + 1, PREG_SPLIT_NO_EMPTY);

        if (count($words) <= $length) {
            return implode(' ', $words);
        }

        return implode(' ', array_slice($words, 0, $length)) . $suffix;
    }
}
