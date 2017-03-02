<?php

/*
 * Copyright (C) 2016 Michael Joyce <mjoyce@sfu.ca>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Nines\UtilBundle\Tests\Services;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Nines\UtilBundle\Services\TitleCaser;

/**
 * Description of TitleCaserTest
 *
 * @author Michael Joyce <mjoyce@sfu.ca>
 */
class TitleCaserTest extends WebTestCase {
    
    /**
     * @var TitleCaser
     */
    protected $caser;
    
    public function setUp() {
        parent::setUp();        
        $this->caser = new TitleCaser();
    }
    
    /**
     * @dataProvider shortWordsData
     */
    public function testShortWords($str, $expected) {
        $this->assertEquals($expected, $this->caser->shortWords($str));
    }
    
    public function shortWordsData() {
        return [
            ['The World', 'The World'],
            ['The Brave And The Bold', 'The Brave and the Bold'],
            ['And Then There Were None', 'And Then There Were None'], 
        ];
    }
    
    /**
     * @dataProvider punctuationData
     */
    public function testPunctuation($str, $expected) {
       $this->assertEquals($expected, $this->caser->punctuation($str)); 
    }
    
    public function punctuationData() {
        return [
            ['The Brave: and the Bold', 'The Brave: And the Bold'],
            ['The Brave : and the bold', 'The Brave: And the bold'],
            ['! A history of Punctuation.', '! A history of Punctuation.'],
        ];
    }
        
    /**
     * @dataProvider statesData
     */
    public function testStates($str, $expected) {
        $this->assertEquals($expected, $this->caser->states($str));
    }
    
    public function statesData() {
        return [
            ['Just Because', 'Just Because'],
            ['Just bc', 'Just BC'],
        ];
    }
    
    /**
     * @dataProvider namesData
     */
    public function testNames($s, $e) {
        $this->assertEquals($e, $this->caser->names($s));
    }
    
    public function namesData() {
        return [
            ["O'donnel and sons", "O'Donnel and sons"],
            ["James Macdonald", "James MacDonald"],
            ["Bob Mcklagen", "Bob McKlagen"],
            ["D'adario", "D'Adario"],
        ];
    }
    
    /**
     * @dataProvider romanData
     */
    public function testRoman($s, $e) {
        $this->assertEquals($e, $this->caser->roman($s));
    }
    
    public function romanData() {
        return [
            ['Elizabeth ii', 'Elizabeth II'],
            ['Poison ivy', 'Poison ivy'],
        ];
    }
    
    /**
     * @dataProvider ordinalsData
     */
    public function testOrdinals($s, $e) {
        $this->assertEquals($e, $this->caser->ordinals($s));
    }
    
    public function ordinalsData() {
        return [
            ['Elizabeth the 2nd', 'Elizabeth the 2nd'],
            ['THE 2ND DAUGHTER', 'THE 2nd DAUGHTER'],
        ];
    }
    
    /**
     * @dataProvider exceptionsData
     */
    public function testExceptions($s, $e) {
        $this->assertEquals($e, $this->caser->exceptions($s));
    }
    
    public function exceptionsData() {
        return [
            ['May West, PHD', 'May West, PhD'],
            ['Billy Cihm', 'Billy CIHM'],
            ['Billie Chimes In', 'Billie Chimes In'],
        ];
    }
    
    /**
     * @dataProvider titleCaseData
     */
    public function testTitleCase($str, $expected) {
        $this->assertEquals($expected, $this->caser->titlecase($str));
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
        ];
    }
    
}
