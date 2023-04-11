Using the Dublin Core Bundle
=====================

Database Tables
---------------

All database table names are prefixed `nines_dc_` to keep them distinct from
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

The `prod` fixtures include all metadata elements defined in the 
[DCMI Terms][dcmi] namespace. They can be loaded as in this example, which 
should be used with caution:

```shell
$ ./bin/console doctrine:fixtures:load --group=prod
```

Entities
--------

Each metadata element (title, creator, etc) corresponds to an `element` entity
which is stored in the database and editable in the usual way through the 
ElementController.

A value is a piece of data associated with an entity. The relationship between
value and entity is indirect, by way of Util Bundle's 
[LinkedEntity](../../UtilBundle/doc/usage.md) system.

Entities which accept Dublin Core Metadata must implement the ValueInterface and
may use the ValueTrait for convenience. The ValueTrait includes a constructor
which must be called for proper initialization of the object. A poem with 
metadata might look like the example below.

```php
use Nines\DublinCoreBundle\Entity\ValueInterface;
use Nines\DublinCoreBundle\Entity\ValueTrait;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity(repositoryClass=PoemRepository::class)
 */
class Poem extends AbstractEntity implements ValueInterface {
    use ValueTrait {
        ValueTrait::__construct as private value_constructor;
    }

    public function __construct() {
        parent::__construct();
        $this->value_constructor();
    }
```

Values are not directly editable via the ValueController. It serves as a 
read-only tool.

Forms
-----

The bundle includes a ValueType form definition which can inject all the 
necessary elements for editing. It can be used as in the example below. Note 
that the ElementRepository must be passed to the `ValueType::add` static method
and that the DublinCoreMapper is required.

> Aside: The DublinCoreMapper may be provided as part of a ChainedMapper set up.
> @TODO provide a link to the documentation for ChainedMapper when it is available.

```php
use App\Entity\Poem;
use Nines\DublinCoreBundle\Form\Mapper\DublinCoreMapper;
use Nines\DublinCoreBundle\Form\ValueType;
use Nines\DublinCoreBundle\Repository\ElementRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Poem form.
 */
class PoemType extends AbstractType {
    private ?ElementRepository $repo = null;

    private ?DublinCoreMapper $mapper = null;

    /**
     * Add form fields to $builder.
     *
     * @param array<string,mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        ValueType::add($builder, array_merge($options, ['repo' => $this->repo]));
        $builder->setDataMapper($this->mapper);
    }

    /**
     * @required
     */
    public function setElementRepository(ElementRepository $repo) : void {
        $this->repo = $repo;
    }

    /**
     * @required
     */
    public function setDublinCoreMapper(DublinCoreMapper $mapper) : void {
        $this->mapper = $mapper;
    }

    /**
     * Define options for the form.
     *
     * Set default, optional, and required options passed to the
     * buildForm() method via the $options parameter.
     */
    public function configureOptions(OptionsResolver $resolver) : void {
        $resolver->setDefaults([
            'data_class' => Poem::class,
        ]);
    }
}
```

Menus
-----

The bundle provides one menu in Menu/Builder.php. It will link to the element 
and value list pages.

```twig
{# templates/base.html.twig #}

    {{ knp_menu_render('nines_dc') }}
```

Templates
---------

Templates are provided in `templates/` and can be 
[easily overridden][override].

A partial template is also provided to embed a table of metadata in a page. It can 
be embedded as shown below. Note that the `entity` parameter is required.

```twig
{# templates/poem/show.html.twig #}

    {% embed '@NinesDublinCore/value/ui/table.html.twig' with {'entity': poem } %}
    {% endembed %}
```

[override]: https://symfony.com/doc/current/bundles/override.html#templates
