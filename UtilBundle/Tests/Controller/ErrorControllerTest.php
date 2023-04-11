<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Tests\Controller;

use DOMDocument;
use DOMXPath;
use Nines\UtilBundle\TestCase\ControllerTestCase;

class ErrorControllerTest extends ControllerTestCase {
    public function testHtmlError() : void {
        $this->client->request('GET', '/_error/400');
        $this->assertResponseStatusCodeSame(400);
        $this->assertSelectorTextContains('h1', '400 This is a sample exception.');
    }

    public function testJsonError() : void {
        $this->client->request('GET', '/_error/400.json');
        $this->assertResponseStatusCodeSame(400);
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(400, $json['status']);
    }

    public function testXmlError() : void {
        $this->client->request('GET', '/_error/400.xml');
        $this->assertResponseStatusCodeSame(400);
        $dom = new DOMDocument('1.0');
        $dom->loadXML($this->client->getResponse()->getContent());
        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query('/response/status/text()');
        $this->assertCount(1, $nodes);
        $this->assertSame('400', $nodes->item(0)->textContent);
    }
}
