<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Tests\Metadata;

use Nines\SolrBundle\Metadata\Metadata;
use Nines\UtilBundle\Tests\BaseCase;

class MetadataTest extends BaseCase {
    protected $meta;

    /**
     * @dataProvider parseData
     *
     * @param mixed $data
     * @param mixed $functionName
     * @param mixed $functionArgs
     */
    public function testParseSimpleCall($data, $functionName, $functionArgs) : void {
        list($name, $args) = $this->meta->parseFunctionCall($data);
        $this->assertSame($functionName, $name);
        $this->assertSame($functionArgs, $args);
    }

    public function parseData() {
        return [
            ['name', 'name', []],
            ['name()', 'name', ['']],
            ['name("")', 'name', ['']],
            ['name(1)', 'name', [1]],
            ['name(1,2)', 'name', [1, 2]],
            ['name( 1 , 2  )', 'name', [1, 2]],
            ['name(true)', 'name', ['true']],
            ['name(false)', 'name', ['false']],
            ['name("abc", null, "def")', 'name', ['abc', 'null', 'def']],
        ];
    }

    protected function setUp() : void {
        parent::setUp();
        $this->meta = new class() extends Metadata {
        };
    }
}
