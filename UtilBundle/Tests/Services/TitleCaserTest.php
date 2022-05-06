<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Tests\Services;

use Nines\UtilBundle\Services\TitleCaser;
use Nines\UtilBundle\TestCase\ServiceTestCase;

class TitleCaserTest extends ServiceTestCase {
    private ?TitleCaser $titleCaser = null;

    public function testSetup() : void {
        $this->assertInstanceOf(TitleCaser::class, $this->titleCaser);
    }

    /**
     * @dataProvider unicodeData
     *
     * @param mixed $str
     * @param mixed $expected
     */
    public function testUnicode($str, $expected) : void {
        $this->assertSame($expected, $this->titleCaser->titlecase($str));
    }

    /**
     * @return string[][]
     */
    public function unicodeData() : array {
        return [
            ['Hæmochromatosis', 'Hæmochromatosis'],
        ];
    }

    /**
     * @dataProvider shortWordsData
     *
     * @param mixed $str
     * @param mixed $expected
     */
    public function testShortWords($str, $expected) : void {
        $this->assertSame($expected, $this->titleCaser->shortWords($str));
    }

    /**
     * @return string[][]
     */
    public function shortWordsData() : array {
        return [
            ['The World', 'The World'],
            ['The Brave And The Bold', 'The Brave and the Bold'],
            ['And Then There Were None', 'And Then There Were None'],
            ['Hæmochromatosis', 'Hæmochromatosis'],
        ];
    }

    /**
     * @dataProvider punctuationData
     *
     * @param mixed $str
     * @param mixed $expected
     */
    public function testPunctuation($str, $expected) : void {
        $this->assertSame($expected, $this->titleCaser->punctuation($str));
    }

    /**
     * @return string[][]
     */
    public function punctuationData() : array {
        return [
            ['The Brave: and the Bold', 'The Brave: And the Bold'],
            ['The Brave : and the bold', 'The Brave: And the bold'],
            ['! A history of Punctuation.', '! A history of Punctuation.'],
            ['Hæmochromatosis', 'Hæmochromatosis'],
        ];
    }

    /**
     * @dataProvider statesData
     *
     * @param mixed $str
     * @param mixed $expected
     */
    public function testStates($str, $expected) : void {
        $this->assertSame($expected, $this->titleCaser->states($str));
    }

    /**
     * @return string[][]
     */
    public function statesData() : array {
        return [
            ['Just Because', 'Just Because'],
            ['Just bc', 'Just BC'],
            ['Hæmochromatosis', 'Hæmochromatosis'],
        ];
    }

    /**
     * @dataProvider namesData
     *
     * @param mixed $s
     * @param mixed $e
     */
    public function testNames($s, $e) : void {
        $this->assertSame($e, $this->titleCaser->names($s));
    }

    /**
     * @return string[][]
     */
    public function namesData() : array {
        return [
            ["O'donnel and sons", "O'Donnel and sons"],
            ['James Macdonald', 'James MacDonald'],
            ['Bob Mcklagen', 'Bob McKlagen'],
            ["D'adario", "D'Adario"],
            ['Hæmochromatosis', 'Hæmochromatosis'],
        ];
    }

    /**
     * @dataProvider romanData
     *
     * @param mixed $s
     * @param mixed $e
     */
    public function testRoman($s, $e) : void {
        $this->assertSame($e, $this->titleCaser->roman($s));
    }

    /**
     * @return string[][]
     */
    public function romanData() : array {
        return [
            ['Elizabeth ii', 'Elizabeth II'],
            ['Poison ivy', 'Poison ivy'],
            ['Hæmochromatosis', 'Hæmochromatosis'],
        ];
    }

    /**
     * @dataProvider ordinalsData
     *
     * @param mixed $s
     * @param mixed $e
     */
    public function testOrdinals($s, $e) : void {
        $this->assertSame($e, $this->titleCaser->ordinals($s));
    }

    /**
     * @return string[][]
     */
    public function ordinalsData() : array {
        return [
            ['Elizabeth the 2nd', 'Elizabeth the 2nd'],
            ['THE 2ND DAUGHTER', 'THE 2nd DAUGHTER'],
            ['Hæmochromatosis', 'Hæmochromatosis'],
        ];
    }

    /**
     * @dataProvider exceptionsData
     *
     * @param mixed $s
     * @param mixed $e
     */
    public function testExceptions($s, $e) : void {
        $this->assertSame($e, $this->titleCaser->exceptions($s));
    }

    /**
     * @return string[][]
     */
    public function exceptionsData() : array {
        return [
            ['May West, PHD', 'May West, PhD'],
            ['Billy Cihm', 'Billy CIHM'],
            ['Billie Chimes In', 'Billie Chimes In'],
            ['Hæmochromatosis', 'Hæmochromatosis'],
        ];
    }

    /**
     * @dataProvider titleCaseData
     *
     * @param mixed $str
     * @param mixed $expected
     */
    public function testTitleCase($str, $expected) : void {
        $this->assertSame($expected, $this->titleCaser->titlecase($str));
    }

    /**
     * @return string[][]
     */
    public function titleCaseData() : array {
        return [
            // start is still capitalized.
            ['THE WORLD', 'The World'],
            // middle stop words aren't.
            ['THE BRAVE AND THE BOLD', 'The Brave and the Bold'],
            // Stop words inside bigger words are capitals.
            ['THEN THERE WERE NONE', 'Then There Were None'],
            // Stop word after a punctuation.
            ['THE BRAVE: AND THE BOLD', 'The Brave: And the Bold'],
            // And punctuation after space is cleaned up.
            ['THE BRAVE : AND THE BOLD', 'The Brave: And the Bold'],
            // starting with punctuation is OK.
            ['! A HISTORY OF PUNCTUATION.', '! A History of Punctuation.'],
            // State abbrs inside words are OK.
            ['AGNES SUBCURRENT', 'Agnes Subcurrent'],
            // Provincial/State abbrs are capitalized.
            ['JUST BC', 'Just BC'],
            // Roman numerals are uppercase.
            ['ELIZABETH II', 'Elizabeth II'],
            // Roman numerals inside words aren't.
            ['POISON IVY', 'Poison Ivy'],
            // Names.
            ["O'DONNEL AND SONS", "O'Donnel and Sons"],
            ['JANE MACDONALD', 'Jane MacDonald'],
            ['BOBBIE MCKLAGEN', 'Bobbie McKlagen'],
            ["D'ADARIO", "D'Adario"],
            // Ordinals.
            ['THE 2ND DAUGHTER', 'The 2nd Daughter'],
            // Exceptions.
            ['MAY WEST, PHD', 'May West, PhD'],
            ['BILLY CIHM', 'Billy CIHM'],
            // Exceptions inside words aren't capitalized.
            ['BILLIE CHIMES HELLO', 'Billie Chimes Hello'],
            ['Hæmochromatosis', 'Hæmochromatosis'],
        ];
    }

    /**
     * @dataProvider sortableData
     *
     * @param mixed $expected
     * @param mixed $given
     */
    public function testSortableTitle($expected, $given) : void {
        $this->assertSame($expected, $this->titleCaser->sortableTitle($given));
    }

    /**
     * @return array<int,array<string>>
     */
    public function sortableData() : array {
        return [
            ['2nd daughter, the', 'The 2nd Daughter'],
            ['a history of punctuation', '! A HISTORY OF PUNCTUATION'],
            ['history of punctuation, a', 'A HISTORY OF PUNCTUATION'],
        ];
    }

    protected function setUp() : void {
        parent::setUp();
        $this->titleCaser = self::$container->get(TitleCaser::class);
    }
}
