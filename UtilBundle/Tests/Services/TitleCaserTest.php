<?php

namespace Nines\UtilBundle\Tests\Services;

use Nines\UtilBundle\Services\TitleCaser;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

class TitleCaserTest extends BaseTestCase {

    /**
     * @var TitleCaser
     */
    private $titleCaser;

    protected function setUp() {
        parent::setUp();
        $this->titleCaser = $this->container->get(TitleCaser::class);
    }

    public function testSetup() {
        $this->assertInstanceOf(TitleCaser::class, $this->titleCaser);
    }

    /**
     * @dataProvider unicodeData
     */
    public function testUnicode($str, $expected) {
        $this->assertEquals($expected, $this->titleCaser->titlecase($str));
    }

    public function unicodeData() {
        return array(
            ['Hæmochromatosis', 'Hæmochromatosis'],
        );
    }

    /**
     * @dataProvider shortWordsData
     */
    public function testShortWords($str, $expected) {
        $this->assertEquals($expected, $this->titleCaser->shortWords($str));
    }

    public function shortWordsData() {
        return [
            ['The World', 'The World'],
            ['The Brave And The Bold', 'The Brave and the Bold'],
            ['And Then There Were None', 'And Then There Were None'],
            ['Hæmochromatosis', 'Hæmochromatosis'],
        ];
    }

    /**
     * @dataProvider punctuationData
     */
    public function testPunctuation($str, $expected) {
        $this->assertEquals($expected, $this->titleCaser->punctuation($str));
    }

    public function punctuationData() {
        return [
            ['The Brave: and the Bold', 'The Brave: And the Bold'],
            ['The Brave : and the bold', 'The Brave: And the bold'],
            ['! A history of Punctuation.', '! A history of Punctuation.'],
            ['Hæmochromatosis', 'Hæmochromatosis'],
        ];
    }

    /**
     * @dataProvider statesData
     */
    public function testStates($str, $expected) {
        $this->assertEquals($expected, $this->titleCaser->states($str));
    }

    public function statesData() {
        return [
            ['Just Because', 'Just Because'],
            ['Just bc', 'Just BC'],
            ['Hæmochromatosis', 'Hæmochromatosis'],
        ];
    }

    /**
     * @dataProvider namesData
     */
    public function testNames($s, $e) {
        $this->assertEquals($e, $this->titleCaser->names($s));
    }

    public function namesData() {
        return [
            ["O'donnel and sons", "O'Donnel and sons"],
            ["James Macdonald", "James MacDonald"],
            ["Bob Mcklagen", "Bob McKlagen"],
            ["D'adario", "D'Adario"],
            ['Hæmochromatosis', 'Hæmochromatosis'],
        ];
    }

    /**
     * @dataProvider romanData
     */
    public function testRoman($s, $e) {
        $this->assertEquals($e, $this->titleCaser->roman($s));
    }

    public function romanData() {
        return [
            ['Elizabeth ii', 'Elizabeth II'],
            ['Poison ivy', 'Poison ivy'],
            ['Hæmochromatosis', 'Hæmochromatosis'],
        ];
    }

    /**
     * @dataProvider ordinalsData
     */
    public function testOrdinals($s, $e) {
        $this->assertEquals($e, $this->titleCaser->ordinals($s));
    }

    public function ordinalsData() {
        return [
            ['Elizabeth the 2nd', 'Elizabeth the 2nd'],
            ['THE 2ND DAUGHTER', 'THE 2nd DAUGHTER'],
            ['Hæmochromatosis', 'Hæmochromatosis'],
        ];
    }

    /**
     * @dataProvider exceptionsData
     */
    public function testExceptions($s, $e) {
        $this->assertEquals($e, $this->titleCaser->exceptions($s));
    }

    public function exceptionsData() {
        return [
            ['May West, PHD', 'May West, PhD'],
            ['Billy Cihm', 'Billy CIHM'],
            ['Billie Chimes In', 'Billie Chimes In'],
            ['Hæmochromatosis', 'Hæmochromatosis'],
        ];
    }

    /**
     * @dataProvider titleCaseData
     */
    public function testTitleCase($str, $expected) {
        $this->assertEquals($expected, $this->titleCaser->titlecase($str));
    }

    public function titleCaseData() {
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
            ["JANE MACDONALD", "Jane MacDonald"],
            ["BOBBIE MCKLAGEN", "Bobbie McKlagen"],
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

}
