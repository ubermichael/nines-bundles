<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\EditorBundle\Tests\Controller;

use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\TestCase\ControllerTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class EditorControllerTest extends ControllerTestCase {
    protected ?string $filePath = null;

    protected ?UploadedFile $upload = null;

    public function testAnonUpload() : void {
        $this->client->request('POST', '/editor/upload/image', [], ['file' => $this->upload]);
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUpload() : void {
        $this->login(UserFixtures::USER);
        $this->client->request('POST', '/editor/upload/image', [], ['file' => $this->upload]);
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $json = json_decode($this->client->getResponse()->getContent());
        $this->assertMatchesRegularExpression('/tmp[a-z0-9_ -]*\.png$/i', $json->location);
    }

    public function testUploadMultiple() : void {
        $this->login(UserFixtures::USER);
        $this->client->request('POST', '/editor/upload/image', [], ['file' => $this->upload, 'bad' => $this->upload]);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testUploadBadName() : void {
        $this->login(UserFixtures::USER);
        $upload = new UploadedFile($this->filePath, 'tmp  .png', 'image/png', null, true);
        $this->client->request('POST', '/editor/upload/image', [], ['file' => $upload]);
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $json = json_decode($this->client->getResponse()->getContent());
        $this->assertMatchesRegularExpression('/tmp[a-z0-9_ -]*\.png$/i', urldecode($json->location));
    }

    public function testView() : void {
        $path = self::$container->getParameter('nines.editor.upload_dir');
        $this->upload->move($path, 'testfile.png');
        $this->client->request('GET', '/editor/upload/image/testfile.png');
        $this->assertResponseIsSuccessful();
    }

    public function testViewBadNameDir() : void {
        $path = self::$container->getParameter('nines.editor.upload_dir');
        $this->upload->move($path, 'testfile.png');
        $this->client->request('GET', '/editor/upload/image/../testfile.png');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testViewBadNameChars() : void {
        $path = self::$container->getParameter('nines.editor.upload_dir');
        $this->upload->move($path, 'testfile.png');
        $this->client->request('GET', '/editor/upload/image/test$file.png');
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testViewNotFound() : void {
        $this->client->request('GET', '/editor/upload/image/cheese.png');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testUserView() : void {
        $this->login(UserFixtures::USER);
        $path = self::$container->getParameter('nines.editor.upload_dir');
        $this->upload->move($path, 'testfile.png');
        $this->client->request('GET', '/editor/upload/image/testfile.png');
        $this->assertResponseIsSuccessful();
    }

    protected function setUp() : void {
        parent::setUp();
        $this->filePath = tempnam(sys_get_temp_dir(), 'nt_');
        imagepng(imagecreatetruecolor(10, 10), $this->filePath);
        $this->upload = new UploadedFile($this->filePath, 'tmp.png', 'image/png', null, true);
    }
}
