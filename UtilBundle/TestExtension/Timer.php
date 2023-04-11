<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\TestExtension;

use PHPUnit\Runner\AfterTestHook;

class Timer implements AfterTestHook {
    protected const SECONDS_ALLOWED = 2;

    public function executeAfterTest(string $test, float $time) : void {
        if ($time >= self::SECONDS_ALLOWED) {
            fwrite(STDERR, "  Test {$test} took {$time} seconds\n");
        }
    }
}
