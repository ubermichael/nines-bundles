<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Tests\Twig;

use Nines\UtilBundle\Twig\TextExtension;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TextExtensionTest extends KernelTestCase
{
    /**
     * @var TextExtension
     */
    private $twig;

    public function testSetup() : void {
        $this->assertInstanceOf(TextExtension::class, $this->twig);
    }

    public function testGetFilters() : void {
        $filters = $this->twig->getFilters();
        $this->assertCount(4, $filters);
    }

    /**
     * @dataProvider ordData
     *
     * @param $decimal
     * @param $character
     */
    public function testOrd($decimal, $character) : void {
        $this->assertSame($decimal, $this->twig->ord($character));
    }

    /**
     * @dataProvider ordData
     *
     * @param $decimal
     * @param $character
     */
    public function testChr($decimal, $character) : void {
        $this->assertSame($character, $this->twig->chr($decimal));
    }

    public function ordData() {
        return [
            [null, null],
            [65, 'A'],
            [97, 'a'],
            [162, '¢'],
            [2361, 'ह'],
            [8364, '€'],
            [54620, '한'],
            [66376, '𐍈'],
        ];
    }

    public function testClassName() : void {
        $this->assertSame(self::class, $this->twig->className($this));
    }

    public function testShortName() : void {
        $this->assertSame('TextExtensionTest', $this->twig->shortName($this));
    }

    protected function setUp() : void {
        parent::setUp();
        self::bootKernel();
        $this->twig = self::$container->get(TextExtension::class);
    }
}
