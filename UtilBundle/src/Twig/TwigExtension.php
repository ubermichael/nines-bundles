<?php

declare(strict_types=1);

/*
 * (c) 2023 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Twig;

use Psr\Log\LoggerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigExtension extends AbstractExtension {
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    public function getFilters() : array {
        return [
            new TwigFilter('camel_title', [$this, 'camelTitle']),
            new TwigFilter('snake_title', [$this, 'snakeTitle']),
        ];
    }

    public function camelTitle(string $name) : string {
        $proper = preg_replace('/([[:lower:]])([[:upper:]])/u', '$1 $2', $name);

        return mb_convert_case($proper, MB_CASE_TITLE);
    }

    public function snakeTitle(string $name) : string {
        $proper = preg_replace('/(.)_(.)/u', '$1 $2', $name);

        return mb_convert_case($proper, MB_CASE_TITLE);
    }
}
