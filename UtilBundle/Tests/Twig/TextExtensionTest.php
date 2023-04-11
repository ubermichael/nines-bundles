<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Tests\Twig;

use Nines\UtilBundle\Twig\TextExtension;
use PHPUnit\Framework\TestCase;

class TextExtensionTest extends TestCase {
    private ?TextExtension $text = null;

    /**
     * @dataProvider camelTitleData
     */
    public function testCamelTitle(string $in, string $expected) : void {
        $this->assertSame($expected, $this->text->camelTitle($in));
    }

    /**
     * @return array<mixed>
     */
    public function camelTitleData() : array {
        return [
            ['eggStuff', 'Egg Stuff'],
            ['', ''],
            ['eggStuffAndMoreStuff', 'Egg Stuff And More Stuff'],
            ['Egg Stuff', 'Egg Stuff'],
            ['egg123', 'Egg123'],
            ['egg123stuff', 'Egg123Stuff'],
        ];
    }

    public function testClassName() : void {
        $this->assertSame(self::class, $this->text->className($this));
    }

    public function testShortName() : void {
        $this->assertSame('TextExtensionTest', $this->text->shortName($this));
    }

    /**
     * @dataProvider ordData
     *
     * @param mixed $in
     * @param mixed $expected
     */
    public function testOrd($in, $expected) : void {
        $this->assertSame($expected, $this->text->ord($in));
    }

    /**
     * @return array<mixed>
     */
    public function ordData() : array {
        return [
            ['a', 97],
            ['A', 65],
            ['€', 0x20AC],
            ['ह', 0x0939],
            ['한', 0xD55C],
        ];
    }

    /**
     * @dataProvider ordData
     *
     * @param mixed $expected
     * @param mixed $in
     */
    public function testChr($expected, $in) : void {
        $this->assertSame($expected, $this->text->chr($in));
    }

    /**
     * @dataProvider bytesData
     *
     * @param mixed $in
     * @param mixed $expected
     */
    public function testByteSize($in, $expected) : void {
        $this->assertSame($expected, $this->text->byteSize($in));
    }

    /**
     * @return array<mixed>
     */
    public function bytesData() : array {
        $kb = 1024;
        $mb = $kb * 1024;
        $gb = $mb * 1024;
        $tb = $gb * 1024;

        return [
            [0, '0b'],
            [99, '99b'],

            [900, '900b'],

            [$kb - 1, '1023b'],
            [$kb, '1Kb'],
            [$kb + 1, '1Kb'],

            [1023 * 950, '949.1Kb'],

            [$mb - 1, '1024Kb'],
            [$mb, '1Mb'],
            [$mb + 1, '1Mb'],

            [$gb - 1, '1024Mb'],
            [$gb, '1Gb'],
            [$gb + 1, '1Gb'],

            [$tb - 1, '1024Gb'],
            [$tb, '1Tb'],
            [$tb + 1, '1Tb'],
        ];
    }

    protected function setUp() : void {
        parent::setUp();
        $this->text = new TextExtension();
    }
}
