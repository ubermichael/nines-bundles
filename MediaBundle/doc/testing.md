Testing Entities Using The Media Bundle
=======================================

Fixtures
--------

### Images

Five CC-licensed sample images are provided in the bundle, and can be used for
testing the file upload things in your application.

```php
class ArtefactFixtures extends Fixture {
    public const FILES = [
        '28213926366_4430448ff7_c.jpg',
        '30191231240_4010f114ba_c.jpg',
        '33519978964_c025c0da71_c.jpg',
        '3632486652_b432f7b283_c.jpg',
        '49654941212_6e3bb28a75_c.jpg',
    ];

    private ?ImageManager $manager = null;

    public function load(ObjectManager $manager) : void {
        $this->manager->setCopy(true);
        for ($i = 1; $i <= 5; $i++) {
            $artefact = new Artefact();
            $manager->persist($artefact);
            $manager->flush();

            $file = self::FILES[$i - 1];
            $upload = new UploadedFile(dirname(__DIR__, 2) . '/lib/Nines/MediaBundle/Tests/data/image/' . $file, $file, 'image/jpeg', null, true);
            $image = new Image();
            $image->setFile($upload);
            $image->setPublic(0 === $i % 2);
            $image->setOriginalName($file);
            $image->setDescription("<p>This is paragraph {$i}</p>");
            $image->setLicense("<p>This is paragraph {$i}</p>");
            $image->setEntity($artefact);
            $manager->persist($image);
            $manager->flush();

            $this->setReference('artefact.' . $i, $artefact);
        }
        $this->manager->setCopy(false);
    }

    /**
     * @required
     */
    public function setManager(ImageManager $manager) : void {
        $this->manager = $manager;
    }
}
```

### Audio

```php
class RecordingFixtures extends Fixture {
    public const FILES = [
        '259692__nsmusic__santur-arpegio.mp3',
        '390587__carloscarty__pan-flute-02.mp3',
        '391691__jpolito__jp-rainloop12.mp3',
        '443027__pramonette__thunder-long.mp3',
        '94934__bletort__taegum-1.mp3',
    ];

    private ?AudioManager $manager = null;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager) : void {
        $this->manager->setCopy(true);
        for ($i = 1; $i <= 5; $i++) {
            $recording = new Recording();
            $manager->persist($recording);
            $manager->flush();

            $file = self::FILES[$i - 1];
            $upload = new UploadedFile(dirname(__DIR__, 2) . '/lib/Nines/MediaBundle/Tests/data/audio/' . $file, $file, 'audio/mp3', null, true);
            $audio = new Audio();
            $audio->setFile($upload);
            $audio->setPublic(0 === $i % 2);
            $audio->setOriginalName($file);
            $audio->setDescription("<p>This is paragraph {$i}</p>");
            $audio->setLicense("<p>This is paragraph {$i}</p>");
            $audio->setEntity($recording);
            $manager->persist($audio);
            $manager->flush();

            $this->setReference('recording.' . $i, $recording);
        }
        $this->manager->setCopy(false);
    }

    /**
     * @required
     */
    public function setManager(AudioManager $manager) : void {
        $this->manager = $manager;
    }
}
```

### PDF

```php
class DocumentFixtures extends Fixture {
    public const FILES = [
        'holmes_1.pdf',
        'holmes_2.pdf',
        'holmes_3.pdf',
        'holmes_4.pdf',
        'holmes_5.pdf',
    ];

    private ?PdfManager $manager = null;

    public function load(ObjectManager $manager) : void {
        $this->manager->setCopy(true);
        for ($i = 1; $i <= 5; $i++) {
            $document = new Document();
            $manager->persist($document);
            $manager->flush();

            $file = self::FILES[$i - 1];
            $upload = new UploadedFile(dirname(__DIR__, 2) . '/lib/Nines/MediaBundle/Tests/data/pdf/' . $file, $file, 'application/pdf', null, true);
            $pdf = new Pdf();
            $pdf->setFile($upload);
            $pdf->setPublic(0 === ($i % 2));
            $pdf->setOriginalName($file);
            $pdf->setDescription("<p>This is paragraph {$i}</p>");
            $pdf->setLicense("<p>This is paragraph {$i}</p>");
            $pdf->setEntity($document);

            $manager->persist($pdf);
            $manager->flush();

            $this->setReference('document.' . $i, $document);
        }
        $this->manager->setCopy(false);
    }

    /**
     * @required
     */
    public function setManager(PdfManager $manager) : void {
        $this->manager = $manager;
    }
}
```

### Links

Links can be created in the fixture directly. There is no upload for links.

```php
class BookmarkFixtures extends Fixture {
    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function load(ObjectManager $manager) : void {
        for ($i = 1; $i <= 5; $i++) {
            $bookmark = new Bookmark();
            $manager->persist($bookmark);
            $manager->flush();

            $link = new Link();
            $link->setUrl('https://example.com/link/' . $i);
            $link->setText('Text ' . $i);
            $link->setEntity($bookmark);
            $manager->persist($link);
            $manager->flush();

            $this->setReference('bookmark.' . $i, $bookmark);
        }
    }
}
```

Tests
-----

Below are some sample tests you can add for your controllers, to make sure they 
are configured correctly.


### Images

```php

    public function testAnonNewImage() : void {
        $crawler = $this->client->request('GET', '/artefact/1/new_image');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserNewImage() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/artefact/1/new_image');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminNewImage() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/artefact/1/new_image');
        $this->assertResponseIsSuccessful();

        $manager = self::$container->get(ImageManager::class);
        $manager->setCopy(true);

        $form = $crawler->selectButton('Create')->form([
            'image[public]' => 1,
            'image[description]' => 'Description',
            'image[license]' => 'License',
        ]);
        $form['image[file]']->upload(dirname(__FILE__, 3) . '/lib/Nines/MediaBundle/Tests/data/image/28213926366_4430448ff7_c.jpg');
        $this->client->submit($form);
        $this->assertResponseRedirects('/artefact/1');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $manager->setCopy(false);
    }

    public function testAnonEditImage() : void {
        $crawler = $this->client->request('GET', '/artefact/1/edit_image/1');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserEditImage() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/artefact/1/edit_image/1');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminEditImage() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/artefact/1/edit_image/1');
        $this->assertResponseIsSuccessful();

        $manager = self::$container->get(ImageManager::class);
        $manager->setCopy(true);

        $form = $crawler->selectButton('Update')->form([
            'image[public]' => 0,
            'image[description]' => 'Updated Description',
            'image[license]' => 'Updated License',
        ]);
        $form['image[newFile]']->upload(dirname(__FILE__, 3) . '/lib/Nines/MediaBundle/Tests/data/image/3632486652_b432f7b283_c.jpg');
        $this->client->submit($form);
        $this->assertResponseRedirects('/artefact/1');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $manager->setCopy(false);
    }

    public function testAnonDeleteImage() : void {
        $crawler = $this->client->request('DELETE', '/artefact/1/delete_image/1');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserDeleteImage() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('DELETE', '/artefact/1/delete_image/1');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminDeleteImage() : void {
        $repo = self::$container->get(ImageRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/artefact/4');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form.delete-form[action="/artefact/4/delete_image/4"]')->form();
        $this->client->submit($form);
        $this->assertResponseRedirects('/artefact/4');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount - 1, $postCount);
    }

    public function testAdminDeleteWrongImage() : void {
        $repo = self::$container->get(ImageRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/artefact/4');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form.delete-form[action="/artefact/4/delete_image/4"]')->form();
        $form->getNode()->setAttribute('action', '/artefact/3/delete_image/4');

        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount, $postCount);
    }
```

### Audio

```php
    public function testAnonNewAudio() : void {
        $crawler = $this->client->request('GET', '/recording/1/new_audio');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserNewAudio() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/recording/1/new_audio');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminNewAudio() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/recording/1/new_audio');
        $this->assertResponseIsSuccessful();

        $manager = self::$container->get(AudioManager::class);
        $manager->setCopy(true);

        $form = $crawler->selectButton('Create')->form([
            'audio[public]' => 1,
            'audio[description]' => 'Description',
            'audio[license]' => 'License',
        ]);
        $form['audio[file]']->upload(dirname(__FILE__, 3) . '/lib/Nines/MediaBundle/Tests/data/audio/443027__pramonette__thunder-long.mp3');
        $this->client->submit($form);
        $this->assertResponseRedirects('/recording/1');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $manager->setCopy(false);
    }

    public function testAnonEditAudio() : void {
        $crawler = $this->client->request('GET', '/recording/1/edit_audio/1');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserEditAudio() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/recording/1/edit_audio/1');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminEditAudio() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/recording/1/edit_audio/1');
        $this->assertResponseIsSuccessful();

        $manager = self::$container->get(AudioManager::class);
        $manager->setCopy(true);

        $form = $crawler->selectButton('Update')->form([
            'audio[public]' => 0,
            'audio[description]' => 'Updated Description',
            'audio[license]' => 'Updated License',
        ]);
        $form['audio[newFile]']->upload(dirname(__FILE__, 3) . '/lib/Nines/MediaBundle/Tests/data/audio/443027__pramonette__thunder-long.mp3');
        $this->client->submit($form);
        $this->assertResponseRedirects('/recording/1');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $manager->setCopy(false);
    }

    public function testAnonDeleteAudio() : void {
        $crawler = $this->client->request('DELETE', '/recording/1/delete_audio/1');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserDeleteAudio() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('DELETE', '/recording/1/delete_audio/1');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminDeleteAudio() : void {
        $repo = self::$container->get(AudioRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/recording/4');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form.delete-form[action="/recording/4/delete_audio/4"]')->form();
        $this->client->submit($form);
        $this->assertResponseRedirects('/recording/4');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount - 1, $postCount);
    }

    public function testAdminDeleteWrongAudio() : void {
        $repo = self::$container->get(AudioRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/recording/4');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form.delete-form[action="/recording/4/delete_audio/4"]')->form();
        $form->getNode()->setAttribute('action', '/recording/3/delete_audio/4');

        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount, $postCount);
    }
```

### PDF

```php

    public function testAnonNewPdf() : void {
        $crawler = $this->client->request('GET', '/document/1/new_pdf');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserNewPdf() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/document/1/new_pdf');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminNewPdf() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/document/1/new_pdf');
        $this->assertResponseIsSuccessful();

        $manager = self::$container->get(PdfManager::class);
        $manager->setCopy(true);

        $form = $crawler->selectButton('Create')->form([
            'pdf[public]' => 1,
            'pdf[description]' => 'Description',
            'pdf[license]' => 'License',
        ]);
        $form['pdf[file]']->upload(dirname(__FILE__, 3) . '/lib/Nines/MediaBundle/Tests/data/pdf/holmes_2.pdf');
        $this->client->submit($form);
        $this->assertResponseRedirects('/document/1');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $manager->setCopy(false);
    }

    public function testAnonEditPdf() : void {
        $crawler = $this->client->request('GET', '/document/1/edit_pdf/1');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserEditPdf() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/document/1/edit_pdf/1');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminEditPdf() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/document/1/edit_pdf/1');
        $this->assertResponseIsSuccessful();

        $manager = self::$container->get(PdfManager::class);
        $manager->setCopy(true);

        $form = $crawler->selectButton('Update')->form([
            'pdf[public]' => 0,
            'pdf[description]' => 'Updated Description',
            'pdf[license]' => 'Updated License',
        ]);
        $form['pdf[newFile]']->upload(dirname(__FILE__, 3) . '/lib/Nines/MediaBundle/Tests/data/pdf/holmes_2.pdf');
        $this->client->submit($form);
        $this->assertResponseRedirects('/document/1');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $manager->setCopy(false);
    }

    public function testAnonDeletePdf() : void {
        $crawler = $this->client->request('DELETE', '/document/1/delete_pdf/1');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserDeletePdf() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('DELETE', '/document/1/delete_pdf/1');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminDeletePdf() : void {
        $repo = self::$container->get(PdfRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/document/4');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form.delete-form[action="/document/4/delete_pdf/4"]')->form();
        $this->client->submit($form);
        $this->assertResponseRedirects('/document/4');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount - 1, $postCount);
    }

    public function testAdminDeleteWrongPdf() : void {
        $repo = self::$container->get(PdfRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/document/4');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form.delete-form[action="/document/4/delete_pdf/4"]')->form();
        $form->getNode()->setAttribute('action', '/document/3/delete_pdf/4');

        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount, $postCount);
    }
```

### Links

Entities containing links must be tested in a different way. Test data must be 
added to the raw form data directly, otherwise the test runner will complain 
that the form fields do not exist.

```php
    public function testAdminEdit() : void {
        $this->login(UserFixtures::ADMIN);
        $formCrawler = $this->client->request('GET', '/bookmark/1/edit');
        $this->assertResponseIsSuccessful();

        $form = $formCrawler->selectButton('Save')->form([
            'bookmark[title]' => 'Updated Title',
        ]);
        $values = $form->getPhpValues();
        $values['bookmark']['links'][1]['url'] = 'https://example.com/path/to/new/link';
        $values['bookmark']['links'][1]['text'] = 'New text';

        $this->client->request($form->getMethod(), $form->getUri(), $values, $form->getPhpFiles());
        $this->assertResponseRedirects('/bookmark/1', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $responseCrawler->filter('a[href="https://example.com/path/to/new/link"]'));
    }
```
