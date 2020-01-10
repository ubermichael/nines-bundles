<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\EditorBundle\Tests\Controller;

use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\Tests\ControllerBaseCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class EditorControllerTest extends ControllerBaseCase {
    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var UploadedFile
     */
    protected $upload;

    public function fixtures() : array {
        return [
            UserFixtures::class,
        ];
    }

    public function testAnonUpload() : void {
        $this->client->request('POST', '/editor/upload/image', [], ['file' => $this->upload]);
        $this->assertSame(302, $this->client->getResponse()->getStatusCode());
    }

    public function testUpload() : void {
        $this->login('user.user');
        $this->client->request('POST', '/editor/upload/image', [], ['file' => $this->upload]);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));
        $json = json_decode($this->client->getResponse()->getContent());
        $this->assertRegExp('/tmp_[a-z0-9_-]*\.png/i', $json->location);
    }

    public function testUploadMultiple() : void {
        $this->login('user.user');
        $this->client->request('POST', '/editor/upload/image', [], ['file' => $this->upload, 'bad' => $this->upload]);
        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
    }

    public function testUploadBadName() : void {
        $this->login('user.user');
        $upload = new UploadedFile($this->filePath, 'tmp  .png', 'image/png', null, true);
        $this->client->request('POST', '/editor/upload/image', [], ['file' => $upload]);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));
        $json = json_decode($this->client->getResponse()->getContent());
        $this->assertRegExp('/tmp___[a-z0-9_-]*\.png/i', $json->location);
    }

    public function testView() : void {
        $path = $this->getContainer()->getParameter('nines.editor.upload_dir');
        $this->upload->move($path, 'testfile.png');
        $this->client->request('GET', '/editor/upload/image/testfile.png');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testViewBadNameDir() : void {
        $path = $this->getContainer()->getParameter('nines.editor.upload_dir');
        $this->upload->move($path, 'testfile.png');
        $this->client->request('GET', '/editor/upload/image/../testfile.png');
        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
    }

    public function testViewBadNameChars() : void {
        $path = $this->getContainer()->getParameter('nines.editor.upload_dir');
        $this->upload->move($path, 'testfile.png');
        $this->client->request('GET', '/editor/upload/image/test$file.png');
        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
    }

    public function testViewNotFound() : void {
        $this->client->request('GET', '/editor/upload/image/cheese.png');
        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
    }

    public function testUserView() : void {
        $this->login('user.user');
        $path = $this->getContainer()->getParameter('nines.editor.upload_dir');
        $this->upload->move($path, 'testfile.png');
        $this->client->request('GET', '/editor/upload/image/testfile.png');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function setUp() : void {
        parent::setUp();
        $this->filePath = tempnam(sys_get_temp_dir(), 'nt_');
        imagepng(imagecreatetruecolor(10, 10), $this->filePath);
        $this->upload = new UploadedFile($this->filePath, 'tmp.png', 'image/png', null, true);
    }
}
