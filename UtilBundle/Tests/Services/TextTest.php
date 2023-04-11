<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Tests\Services;

use Nines\UtilBundle\Services\Text;
use Nines\UtilBundle\TestCase\ServiceTestCase;

class TextTest extends ServiceTestCase {
    private ?Text $text = null;

    public function testSetup() : void {
        $this->assertInstanceOf(Text::class, $this->text);
    }

    /**
     * @dataProvider plainData
     *
     * @param mixed $str
     * @param mixed $expected
     */
    public function testPlain($str, $expected) : void {
        $this->assertSame($expected, $this->text->plain($str));
    }

    /**
     * @return string[][]
     */
    public function plainData() : array {
        return [
            ['plain text and stuff.', 'plain text and stuff.'],
            ['<b>Fancy stuff.', 'Fancy stuff.'],
            ['Fancy&nbsp;stuff', 'Fancy stuff'],
            ['  Spaces!  ', 'Spaces!'],
            ['Fréchêt', 'Fréchêt'],
            ['Fr&eacute;chet', 'Fréchet'],
            ['Fr&eacute;chét', 'Fréchét'],
        ];
    }

    /**
     * @dataProvider searchHighlightData
     *
     * @param mixed $str
     * @param mixed $kw
     * @param mixed $expected
     */
    public function testSearchHighlight($str, $kw, $expected) : void {
        $this->assertSame($expected, $this->text->searchHighlight($str, $kw));
    }

    /**
     * @return array<int,array<int,mixed>>
     */
    public function searchHighlightData() : array {
        return [
            ['chilli cheese fries', 'asparagus', []],
            ['chilli cheese fries', 'chilli', ['<mark>chilli</mark> cheese fries']],
            ['chilli cheese fries', 'cheese', ['chilli <mark>cheese</mark> fries']],
            ['chilli cheese fries', 'fries', ['chilli cheese <mark>fries</mark>']],
            ['chilli cheese chilli fries', 'chilli', [
                '<mark>chilli</mark> cheese <mark>chilli</mark> fries',
            ]],
        ];
    }

    /**
     * @dataProvider slugData
     *
     * @param mixed $str
     * @param mixed $expected
     */
    public function testSlug($str, $expected) : void {
        $this->assertSame($expected, $this->text->slug($str));
    }

    /**
     * @return string[][]
     */
    public function slugData() : array {
        return [
            ['', ''],
            ['The Dinner', 'the-dinner'],
            ['  The Dinner  ', 'the-dinner'],
            [' Röb ', 'rob'],
            ['Robero . . .', 'robero'],
            ['Robero...', 'robero'],
            ['Rœb', 'roeb'],
            ['strauß', 'strauss'],
            ['Part 2.1', 'part-2.1'],
            ['Question? Yes.', 'question-yes'],
            ['Question #1a', 'question-1a'],
            ['Question-1', 'question-1'],
            ['Q -1', 'q-1'],
            ['Q_1', 'q_1'],
        ];
    }

    /**
     * @dataProvider slugSeparatorData
     *
     * @param mixed $str
     * @param mixed $expected
     * @param mixed $separator
     */
    public function testSlugSeparator($str, $expected, $separator) : void {
        $this->assertSame($expected, $this->text->slug($str, $separator));
    }

    /**
     * @return string[][]
     */
    public function slugSeparatorData() : array {
        return [
            ['', '', '.'],
            ['The Dinner', 'the-dinner', '-'],
            ['Part 2.1', 'part_2.1', '_'],
            ['Question? Yes.', 'question.yes', '.'],
            ['Question? Yes.', 'question/yes', '/'],
            ['Question #1a', 'question1a', ''],
            ['Multi char seps', 'multi---char---seps', '---'],
            ['Mash Words', 'mashwords', ''],
        ];
    }

    /**
     * @dataProvider trimData
     *
     * @param mixed $expected
     * @param mixed $len
     * @param mixed $string
     */
    public function testTrim($expected, $len, $string) : void {
        $this->assertSame($expected, $this->text->trim($string, $len));
    }

    /**
     * @return array<int,array<int,mixed>>
     */
    public function trimData() : array {
        return [
            ['This is a...', 3, 'This is a test of the emergency broadcast system.'],
            ['This is a...', 3, '   This is a test of the emergency broadcast system.'],
            ['This. is- a...', 3, 'This. is- a test of the emergency broadcast system.'],
            ['This is a...', 3, '<p>This <b>is</b> a test of the emergency broadcast system.'],
            ['This is a...', 3, 'This&nbsp;is a test of the emergency broadcast system.'],
            ['Thés is a...', 3, 'Th&eacute;s is a test of the emergency broadcast system.'],
            ['Thés iſ a...', 3, 'Thés iſ a test of the emergency broadcast system.'],
        ];
    }

    protected function setUp() : void {
        parent::setUp();
        $this->text = self::$container->get(Text::class);
    }
}
