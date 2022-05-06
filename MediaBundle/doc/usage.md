Using the Media Bundle
=====================

Database Tables
---------------

All database table names are prefixed `nines_media_` to keep them distinct from
your appliaction. Once the bundle is enabled, you can create the tables with a
doctrine migration.

Migrations are not included in the bundles, you will need to generate them
yourself. For example,

```shell
$ ./bin/console doctrine:migrations:diff -n
```

Then carefully review the generated migration file.

Data Fixtures
-------------

For convenience, a few data fixtures are pre-configured and ready to be loaded.
There are three types of fixtures: `test` for testing, `dev` for development,
and `prod` with content suitable for production.

```shell
$ ./bin/console doctrine:fixtures:load --group=prod
```

Entities
--------

This bundle supports image and audio files, PDFs, and links. 

### Images

Add images to an entity with the ImageContainerInterface and ImageContainerTrait
like so:

```php
namespace App\Entity;

use Nines\MediaBundle\Entity\ImageContainerInterface;
use Nines\MediaBundle\Entity\ImageContainerTrait;
use Nines\UtilBundle\Entity\AbstractEntity;

class Artefact extends AbstractEntity implements ImageContainerInterface {
    use ImageContainerTrait {
        ImageContainerTrait::__construct as private image_constructor;
    }
    
    // ...
    
    public function __construct() {
        parent::__construct();
        $this->image_constructor();
    }
}
```

Image metadata will be stored in the nines_media_image table.

### Audio

Add audio files to an entity with the AudioContainerInterface and AudioContainerTrait
like so:

```php
namespace App\Entity;

use Nines\MediaBundle\Entity\AudioContainerInterface;
use Nines\MediaBundle\Entity\AudioContainerTrait;
use Nines\UtilBundle\Entity\AbstractEntity;

class Recording extends AbstractEntity implements AudioContainerInterface {
    use AudioContainerTrait {
        AudioContainerTrait::__construct as private audio_constructor;
    }

    /**
     * @ORM\Column(type="string")
     */
    private ?string $title = null;

    public function __construct() {
        parent::__construct();
        $this->audio_constructor();
    }
```

Audio metadata will be stored in the nines_media_audio table.

### PDF

Add PDFs to an entity with the PdfContainerInterface and PdfContainerTrait
like so:

```php
namespace App\Entity;

use Nines\MediaBundle\Entity\PdfContainerInterface;
use Nines\MediaBundle\Entity\PdfContainerTrait;
use Nines\UtilBundle\Entity\AbstractEntity;

class Document extends AbstractEntity implements PdfContainerInterface {
    use PdfContainerTrait {
    PdfContainerTrait::__construct as private pdf_constructor;
    }
    
    // ...

    public function __construct() {
        parent::__construct();
        $this->pdf_constructor();
    }
```

### Links

Links are not media files and do not involve uploads, but they're included in 
this bundle for historical reasons. Add them to an entity with the interface and
trait.

```php
namespace App\Entity;

use Nines\MediaBundle\Entity\LinkContainerInterface;
use Nines\MediaBundle\Entity\LinkContainerTrait;
use Nines\UtilBundle\Entity\AbstractEntity;

class Bookmark extends AbstractEntity implements LinkableInterface {
    use LinkableTrait {
        LinkableTrait::__construct as private linkable_constructor;
    }

    // ...

    public function __construct() {
        parent::__construct();
        $this->linkable_constructor();
    }
```

Controllers
-----------

Controller traits are provided to make uploading files easier.

In the examples below, the controller defines the routes for manipulating images
associated with an artefact. The controller passes most of the work off to the
trait.

The controller traits use forms defined in the bundle, but they can be 
[overridden][override].

### Images

```php
/**
 * @Route("/artefact")
 */
class ArtefactController extends AbstractController implements PaginatorAwareInterface {
    use ImageControllerTrait;
    use PaginatorTrait;
    
    // ...
    
    /**
     * @Route("/{id}/new_image", name="artefact_new_image", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @throws Exception
     */
    public function newImage(Request $request, EntityManagerInterface $em, Artefact $artefact) : Response {
        $context = $this->newImageAction($request, $em, $artefact, 'artefact_show');
        if ($context instanceof RedirectResponse) {
            return $context;
        }

        return $this->render('artefact/new_image.html.twig', array_merge($context, [
            'artefact' => $artefact,
        ]));
    }

    /**
     * @Route("/{id}/edit_image/{image_id}", name="artefact_edit_image", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @ParamConverter("image", options={"id" = "image_id"})
     *
     * @throws Exception
     */
    public function editImage(Request $request, EntityManagerInterface $em, Artefact $artefact, Image $image) : Response {
        $context = $this->editImageAction($request, $em, $artefact, $image, 'artefact_show');
        if ($context instanceof RedirectResponse) {
            return $context;
        }

        return $this->render('artefact/edit_image.html.twig', array_merge($context, [
            'artefact' => $artefact,
        ]));
    }

    /**
     * @Route("/{id}/delete_image/{image_id}", name="artefact_delete_image", methods={"DELETE"})
     * @ParamConverter("image", options={"id" = "image_id"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     */
    public function deleteImage(Request $request, EntityManagerInterface $em, Artefact $artefact, Image $image) : RedirectResponse {
        return $this->deleteImageAction($request, $em, $artefact, $image, 'artefact_show');
    }
```

### Audio

```php
/**
 * @Route("/recording")
 */
class RecordingController extends AbstractController implements PaginatorAwareInterface {
    use AudioControllerTrait;
    use PaginatorTrait;

    // ...


    /**
     * @Route("/{id}/new_audio", name="recording_new_audio", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @throws Exception
     */
    public function newAudio(Request $request, EntityManagerInterface $em, Recording $recording) : Response {
        $context = $this->newAudioAction($request, $em, $recording, 'recording_show');
        if ($context instanceof RedirectResponse) {
            return $context;
        }

        return $this->render('recording/new_audio.html.twig', array_merge($context, [
            'recording' => $recording,
        ]));
    }

    /**
     * @Route("/{id}/edit_audio/{audio_id}", name="recording_edit_audio", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @ParamConverter("audio", options={"id" = "audio_id"})
     *
     * @throws Exception
     */
    public function editAudio(Request $request, EntityManagerInterface $em, Recording $recording, Audio $audio, AudioManager $fileUploader) : Response {
        $context = $this->editAudioAction($request, $em, $recording, $audio, 'recording_show');
        if ($context instanceof RedirectResponse) {
            return $context;
        }

        return $this->render('recording/edit_audio.html.twig', array_merge($context, [
            'recording' => $recording,
        ]));
    }

    /**
     * @Route("/{id}/delete_audio/{audio_id}", name="recording_delete_audio", methods={"DELETE"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @ParamConverter("audio", options={"id" = "audio_id"})
     *
     * @throws Exception
     */
    public function deleteAudio(Request $request, EntityManagerInterface $em, Recording $recording, Audio $audio) : RedirectResponse {
        return $this->deleteAudioAction($request, $em, $recording, $audio, 'recording_show');
    }
}
```

### PDF

```php
/**
 * @Route("/document")
 */
class DocumentController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;
    use PdfControllerTrait;

    // ... 

    /**
     * @Route("/{id}/new_pdf", name="document_new_pdf", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @throws Exception
     */
    public function newPdf(Request $request, EntityManagerInterface $em, Document $document) : Response {
        $context = $this->newPdfAction($request, $em, $document, 'document_show');
        if ($context instanceof RedirectResponse) {
            return $context;
        }

        return $this->render('document/new_pdf.html.twig', array_merge($context, [
            'document' => $document,
        ]));
    }

    /**
     * @Route("/{id}/edit_pdf/{pdf_id}", name="document_edit_pdf", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @ParamConverter("pdf", options={"id" = "pdf_id"})
     *
     * @throws Exception
     */
    public function editPdf(Request $request, EntityManagerInterface $em, Document $document, Pdf $pdf, PdfManager $fileUploader) : Response {
        $context = $this->editPdfAction($request, $em, $document, $pdf, 'document_show');
        if ($context instanceof RedirectResponse) {
            return $context;
        }

        return $this->render('document/edit_pdf.html.twig', array_merge($context, [
            'document' => $document,
        ]));
    }

    /**
     * @Route("/{id}/delete_pdf/{pdf_id}", name="document_delete_pdf", methods={"DELETE"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @ParamConverter("pdf", options={"id" = "pdf_id"})
     *
     * @throws Exception
     */
    public function deletePdf(Request $request, EntityManagerInterface $em, Document $document, Pdf $pdf) : RedirectResponse {
        return $this->deletePdfAction($request, $em, $document, $pdf, 'document_show');
    }
}    
```

### Links

Managing links is a little more complex. The bundle provides a LinkType partial
form and a DataMapper to make sure the links are properly associated with the
entities.

This is done so that the link form can be embedded in an entity form type. 

#### LinkType form partial

```php
class BookmarkType extends AbstractType {
    private ?LinkableMapper $mapper = null;

    /**
     * Add form fields to $builder.
     *
     * @param array<string,mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        // ...
        LinkableType::add($builder, $options);
        $builder->setDataMapper($this->mapper);
    }
    
    // ...

    /**
     * @required
     */
    public function setLinkableMapper(LinkableMapper $mapper) : void {
        $this->mapper = $mapper;
    }
}
```

Additional Controllers
----------------------

The bundle also provides read-only access to the media entities to users who
are granted `ROLE_MEDIA_ADMIN`. There is no general usefulness to these 
controllers, but they are helpful for debugging and data quality checking.

Menus
-----

The bundle provides one menu in Menu/Builder.php. It will link to the media
controllers described above.

```twig
{# templates/base.html.twig #}

    {{ knp_menu_render('nines_media') }}
```

Templates
---------

Templates are provided in `templates/` and can be 
[easily overridden][override].

### Image templates

The example below includes all the necessary controls to add, edit, view, and
delete images from an artefact entity. The `list.html.twig` template will
display the image thumbnails with links to larger versions.

```twig
    <h2>Artefact Images</h2>
    {% if is_granted('ROLE_CONTENT_ADMIN') %}
        <div class='btn-toolbar pull-right'>
            <div class='btn-group'>
                <a href="{{ path('artefact_new_image', {'id': artefact.id }) }}" class="btn btn-default">
                    <span class="glyphicon glyphicon-plus"></span> Add Image </a>
            </div>
        </div>
        <div class='clearfix'></div>
    {% endif %}

    {% include '@NinesMedia/image/ui/list.html.twig' with {
        'container': artefact,
        'path_new': 'artefact_new_image',
        'path_edit': 'artefact_edit_image',
        'path_delete': 'artefact_delete_image',
    } %}
```

### Audio templates

Audio templates are provided for playing the audio files, and includes the 
relevant controls.

```twig
    {% include '@NinesMedia/audio/ui/player.html.twig' with {
        'container': recording,
        'new_path': 'recording_new_audio',
        'edit_path': 'recording_edit_audio',
        'delete_path': 'recording_delete_audio'
    } %}
```

### PDF templates

PDF thumbnails can be embedded in a template and will link to a PDF viewer.

```twig
    <h2>Document PDFs</h2>
    {% if is_granted('ROLE_CONTENT_ADMIN') %}
        <div class='btn-toolbar pull-right'>
            <div class='btn-group'>
                <a href="{{ path('document_new_pdf', {'id': document.id }) }}" class="btn btn-default">
                    <span class="glyphicon glyphicon-plus"></span> Add Pdf </a>
            </div>
        </div>
        <div class='clearfix'></div>
    {% endif %}
    {% embed '@NinesMedia/pdf/ui/list.html.twig' with {
        'container': document,
        'path_delete': 'document_delete_pdf',
        'path_edit': 'document_edit_pdf',
    } %}
    {% endembed %}
```

### Link templates

Links can be embedded in a web page. No controls for editing the links are 
provided, as they should be edited with the main entity form type.

```twig
    <h2>Links</h2>
    {% embed '@NinesMedia/link/partial/list.html.twig' with {
        'entity': bookmark } %}
    {% endembed %}
```

[override]: https://symfony.com/doc/current/bundles/override.html#templates
