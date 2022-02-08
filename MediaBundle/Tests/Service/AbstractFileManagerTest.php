<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Tests\Service;

use Nines\MediaBundle\Service\AbstractFileManager;
use PHPUnit\Framework\TestCase;

class AbstractFileManagerTest extends TestCase {
    /**
     * @dataProvider parseSizeData
     */
    public function testParseSize(float $expected, string $given) : void {
        $this->assertSame($expected, AbstractFileManager::parseSize($given));
    }

    /**
     * @return array<int,mixed>
     */
    public function parseSizeData() : array {
        return [
            [100.0, '100'],
            [10.0, '10b'],
            [1024, '1k'],
            [2097152.0, '2m'],
            [3 * 1024 ** 3, '3g'],
            [4 * 1024 ** 4, '4t'],
            [5 * 1024 ** 5, '5p'],
            [6 * 1024 ** 6, '6e'],
            [7 * 1024 ** 7, '7z'],
            [8 * 1024 ** 8, '8y'], // That's a yotta bytes

            [512.0, '0.5k'],
            [222822400.0, '212.5m'],
            [4194304.0, '4096k'],

            [10.0, '10 b'],
            [1024, '1kb'],
        ];
    }

    /**
     * @dataProvider bytesToSizeData
     */
    public function testBytesToSize(string $expected, float $given) : void {
        $this->assertSame($expected, AbstractFileManager::bytesToSize($given));
    }

    /**
     * @return array<int,mixed>
     */
    public function bytesToSizeData() : array {
        return [
            ['1b', 1],
            ['10b', 10],
            ['1Kb', 1024],
            ['1023b', 1023],
            ['1Mb', 1024 ** 2],
            ['1Gb', 1024 ** 3],
            ['1Tb', 1024 ** 4],
            ['1.5Mb', 1.5 * 1024 ** 2],
        ];
    }
}
