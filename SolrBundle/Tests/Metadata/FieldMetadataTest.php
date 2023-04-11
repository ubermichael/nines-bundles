<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Tests\Metadata;

use DateTimeImmutable;
use DateTimeInterface;
use Nines\SolrBundle\Metadata\FieldMetadata;
use Nines\UtilBundle\Entity\AbstractEntity;
use PHPUnit\Framework\TestCase;

class FieldMetadataTest extends TestCase {
    private ?AbstractEntity $entity = null;

    public function testFetch() : void {
        $meta = new FieldMetadata();
        $meta->setFieldName('field');
        $meta->setGetter('exec');
        $this->assertSame('called', $meta->fetch($this->entity));
    }

    public function testFetchArgs() : void {
        $meta = new FieldMetadata();
        $meta->setFieldName('field');
        $meta->setGetter('execArgs("ab", "cd")');
        $this->assertSame('called ab cd', $meta->fetch($this->entity));
    }

    public function testFetchNull() : void {
        $meta = new FieldMetadata();
        $meta->setFieldName('field');
        $meta->setGetter('execNull');
        $this->assertNull($meta->fetch($this->entity));
    }

    public function testMutator() : void {
        $meta = new FieldMetadata();
        $meta->setFieldName('field');
        $meta->setGetter('dt');
        $meta->setMutator('getTimestamp');
        $this->assertSame(1612182896, $meta->fetch($this->entity));
    }

    public function testMutatorArgs() : void {
        $meta = new FieldMetadata();
        $meta->setFieldName('field');
        $meta->setGetter('dt');
        $meta->setMutator('format("Y-m-d")');
        $this->assertSame('2021-02-01', $meta->fetch($this->entity));
    }

    public function testFilters() : void {
        $meta = new FieldMetadata();
        $meta->setFieldName('field');
        $meta->setGetter('data');
        $meta->setFilters(['strip_tags', 'strtoupper']);
        $this->assertSame('ABC', $meta->fetch($this->entity));
    }

    public function testFilterArgs() : void {
        $meta = new FieldMetadata();
        $meta->setFieldName('field');
        $meta->setGetter('data');
        $meta->setFilters(['substr(3,3)']);
        $this->assertSame('abc', $meta->fetch($this->entity));
    }

    protected function setUp() : void {
        parent::setUp();
        $this->entity = new class() extends AbstractEntity {
            public function __toString() {
                return 'ok';
            }

            public function exec() : string {
                return 'called';
            }

            public function execArgs(string $a, string $b) : string {
                return 'called ' . $a . ' ' . $b;
            }

            public function dt() : DateTimeInterface {
                return new DateTimeImmutable('2021-02-01 12:34:56');
            }

            public function data() : string {
                return '<a>abc</a>';
            }

            public function execNull() : ?string {
                return null;
            }
        };
    }
}
