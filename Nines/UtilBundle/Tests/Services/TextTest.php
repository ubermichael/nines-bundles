<?php

namespace Nines\UtilBundle\Tests\Services;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Nines\UtilBundle\Services\Slugger;
use Nines\UtilBundle\Services\Text;

class SluggerTest extends WebTestCase
{

    /**
     * @var Slugger
     */
    private $text;

    public function setUp() {
        parent::setUp();
        $this->text = new Text();
    }

    public function testSetup() {
        $this->assertInstanceOf(Text::class, $this->text);
    }

    /**
     * @dataProvider plainData
     */
    public function testPlain($str, $expected) {
        $this->assertEquals($expected, $this->text->plain($str));
    }
    
    public function plainData() {
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
     */
    public function testSearchHighlight($str, $kw, $expected) {
        $this->assertEquals($expected, $this->text->searchHighlight($str, $kw));
    }
    
    public function searchHighlightData() {
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
     */
    public function testSlug($str, $expected) {
        $this->assertEquals($expected, $this->text->slug($str));
    }
 
    public function slugData() {
        return [
            [null, null],
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
     */
    public function testSlugSeparator($str, $expected, $separator) {
        $this->assertEquals($expected, $this->text->slug($str, $separator));        
    }
    
    public function slugSeparatorData() {
        return [
            [null, null, null],
            ['', '', '.'],
            ['The Dinner', 'the-dinner', '-'],
            ['Part 2.1', 'part_2.1', '_'],
            ['Question? Yes.', 'question.yes', '.'],
            ['Question? Yes.', 'question/yes', '/'],
            ['Question #1a', 'question1a', ''],
            ['Multi char seps', 'multi---char---seps', '---'],
            ['Mash Words', 'mashwords', null],
            ['Mash Words', 'mashwords', ''],
        ];
    }
    
    /**
     * @dataProvider trimData
     */
    public function testTrim($expected, $len, $string) {
        $this->assertEquals($expected, $this->text->trim($string, $len));
    }
    
    public function trimData() {
        return [
            ["This is a...", 3, "This is a test of the emergency broadcast system."],
            ["This is a...", 3, "   This is a test of the emergency broadcast system."],
            ["This. is- a...", 3, "This. is- a test of the emergency broadcast system."],
            ["This is a...", 3, "<p>This <b>is</b> a test of the emergency broadcast system."],
            ["This is a...", 3, "This&nbsp;is a test of the emergency broadcast system."],
            ["Thés is a...", 3, "Th&eacute;s is a test of the emergency broadcast system."],
            ["Thés iſ a...", 3, "Thés iſ a test of the emergency broadcast system."],
        ];
    }
    
    
}
