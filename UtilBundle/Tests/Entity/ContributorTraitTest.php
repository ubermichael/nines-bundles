<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Tests\Entity;

use DateTimeImmutable;
use Nines\UtilBundle\Entity\AbstractEntity;
use Nines\UtilBundle\Entity\ContributorInterface;
use Nines\UtilBundle\Entity\ContributorTrait;
use PHPUnit\Framework\TestCase;

class ContributorTraitTest extends TestCase {
    private ?object $entity = null;

    public function testAddContribution() : void {
        $this->entity->addContribution(new DateTimeImmutable('2020-01-01 10:34'), 'Bob');
        $this->assertSame([['date' => '2020-01-01', 'name' => 'Bob']], $this->entity->rawData());
    }

    public function testGetEmptyContributions() : void {
        $this->assertCount(0, $this->entity->getContributions());
    }

    public function testGetContributions() : void {
        $data = [
            ['date' => '2019-01-01', 'name' => 'Charlie'],
            ['date' => '2020-01-01', 'name' => 'Bob'],
            ['date' => '2019-01-01', 'name' => 'Bob'],
            ['date' => '2020-01-01', 'name' => 'Alice'],
            ['date' => '2019-01-02', 'name' => 'Charlie'],
        ];

        $expected = [
            ['date' => '2020-01-01', 'name' => 'Alice'],
            ['date' => '2020-01-01', 'name' => 'Bob'],
            ['date' => '2019-01-02', 'name' => 'Charlie'],
            ['date' => '2019-01-01', 'name' => 'Bob'],
            ['date' => '2019-01-01', 'name' => 'Charlie'],
        ];

        $this->entity->setContributions($data);
        $this->assertSame($expected, $this->entity->getContributions());
    }

    public function testAddContributions() : void {
        $this->entity->addContribution(new DateTimeImmutable('2020-01-01'), 'Bill');
        $this->assertCount(1, $this->entity->getContributions());
        $this->entity->addContribution(new DateTimeImmutable('2020-01-02'), 'Bill');
        $this->assertCount(2, $this->entity->getContributions());
        $this->entity->addContribution(new DateTimeImmutable('2020-01-02'), 'Charlie');
        $this->assertCount(3, $this->entity->getContributions());
    }

    public function testAddDuplicateContributions() : void {
        $this->entity->addContribution(new DateTimeImmutable('2020-01-01'), 'Bill');
        $this->assertCount(1, $this->entity->getContributions());
        $this->entity->addContribution(new DateTimeImmutable('2020-01-01'), 'Bill');
        $this->assertCount(1, $this->entity->getContributions());
        $this->entity->addContribution(new DateTimeImmutable('2020-01-01'), 'Charlie');
        $this->assertCount(2, $this->entity->getContributions());
    }

    protected function setUp() : void {
        parent::setUp();
        $this->entity = new class() extends AbstractEntity implements ContributorInterface {
            use ContributorTrait;

            public function __toString() {
                return 'string';
            }

            /**
             * @return array<int,array<string,string>>
             */
            public function rawData() : array {
                return $this->contributions;
            }
        };
    }
}
