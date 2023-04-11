Annotations in the Solr Bundle
==============================

This bundle used Doctrine-style [annotations][doctrine-annotations] on each entity that
should be indexed in solr. These annotations describe the types of each field
and how the data for those fields is fetched from the entity.

All examples below assume that the following namespace declaration is present

```php
use Nines\SolrBundle\Annotation as Solr;
```

### Document

`@Solr\Document` applies to entity classes that should be indexed in Solr.
Additional annotations are necessary for any data to actually be indexed.
This annotation can include `@Solr\CopyField` and `@Solr\ComputedField` 
annotations as well.

```php
/**
 * @Solr\Document(
 *     copyField={
 *         @Solr\CopyField(from={"main", "sub"}, to="title", type="texts"),
 *         @Solr\CopyField(from={"main", "sub", "description"}, to="content", type="texts")
 *     },
 *     computedFields={
 *         @Solr\ComputedField(name="tax_price", getter="getPriceWithTax", type="float")
 *      }
 * )
 */
class Title extends {
 /// ...
}
```

> The Doctrine library used to parse the annotations has some syntatic sugar to
allow simplifying the `@Solr\CopyField` and `@Solr\ComputedFields` declarations.
That syntax is brittle and likely to change. For best results, use the full 
declaration syntax described above.

### Id

Solr documents must have identifiers, and those identifiers connect the document
back to the entity. The `@Solr\Id` annotation tells the Solr indexer how to find
the ID. It must be applied to one property in the indexed class.

```php
    /**
     * The entity's ID.
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Solr\Id
     */
    protected ?int $id = null;
```

The actual ID stored in Solr will be something like `App\Entity\Title:1234` to
guarantee that the ID is unique. The Solr bundle will also use this information 
to fetch Title #1234 from the database.

### Field

Entity properities with the `@Solr\Field` annotation will be indexed in Solr. 
Field annotations support the following properties

| Name      | Type             | Required | Description                                                                            |
|-----------|------------------|----------|----------------------------------------------------------------------------------------|
| `type`    | string           | Yes      | One of the type descriptors listed below                                               |
| `boost`   | float            | No       | Unused. In future will be used to set the field boost in queries                       |
| `getter`  | string           | No       | The name of the function to use to fetch the data. Defaults to getFieldName            |
| `mutator` | string           | No       | If specified, the named function will be called on the *object* returned by the getter |
| `filters` | array of strings | No       | List of functions that will be used to filter the data.                                | 

In the example below, `description` is an indexed text field. The field data 
will be run through the `strip_tags` function and through `html_entity_decode`. 
The data is always the first argument to the filter functions, and additional 
arguments maybe be specified. 

```php
    /**
     * @ORM\Column(type="text")
     * @Solr\Field(type="text", boost=0.5, filters={"strip_tags", "html_entity_decode(51, 'UTF-8')"})
     */
    private ?string $description = null;
```

> 51 is a magic number in this example. It is `ENT_QUOTES|ENT_HTML5`. The Solr
bundle is not able to handle anything other than simple constants as arguments. 
See the flags in [html_entity_decode][html_entity_decode] for more information.

In the next example the method `getCreated` returns a DateTimeInterface object 
which must be converted to a string. The `mutator` property is used to 
accomplish this. 

```php
    /**
     * @Solr\Field(type="datetime", mutator="format('Y-m-d\TH:i:s\Z')")
     */
    protected ?DateTimeInterface $created = null;
```

#### Field Types

**Single values**
 
- `boolean` - true or false or null. Nulls are considered false in Solr.
- `date` - A string containing a date formatted YYYY-MM-DD.
- `datetime` - A string containing a date & time formatted exactly as `Y-m-d\TH:i:s\Z` where `\T` and `\Z` are literal T and Z characters.
- `double` - A floating point number in double precision
- `float` - A floating point number
- `integer` - 32-bit signed integer
- `long` - 64-bit signed integer
- `location` - A latitude/longitude coordinate pair in a string formatted as `lat,lon`
- `string` - Any string
- `text` - Generic text field
- `text_en` - Generic English text field with some special processing
- `text_sortable` - Generic text field that is sortable on the first 1024 characters

**Multiple values**

Arrays are also supported with the same types as above. Use one of these 
pluralized field type names with the same semantics as above.

- `booleans`
- `dates` 
- `datetimes` 
- `doubles` 
- `floats`
- `integers`
- `longs`
- `strings` 
- `texts` 
- `texts_en` 
- `texts_sortable` 

> Text-types cannot be used for sorting or for faceting/filtering in a reliable
way. `texts_sortable` may work depending on the version of Solr.

### CopyField

The `@Solr\CopyField` anotation is used inside the `@Solr\Document` annotation 
to create "catchall" fields that combine multple fields in one. In this example
we create a Solr title field that contains the main and sub title fields. This 
allows a simpler search for titles.

```php
/**
 * @Solr\Document(
 *     copyField={
 *         @Solr\CopyField(from={"main", "sub"}, to="title", type="texts")
 *     }
 * )
*/
```

Multiple copy field definitions are supported.

### ComputedField

The `@Solr\ComputedField` annotation is used for instances where indexed data 
must be calculated from other fields. Use cases include indexing data available 
via complex foreign key relationships or combining multiple fields in one.

In the example below, the latitude and longitude properties are combined into 
a single location field called coordinates.

```php
/**
* @Solr\Document(
*     computedFields={
*         @Solr\ComputedField(name="coordinates", type="location", getter="getCoordinates")
*     }
* )
*/
class Place {
    public function getCoordinates() : string {
        return $this->getLatitude . ',' . $this->getLongitude;
    }
}
```

## Foreign Key Relationships

It is possible to index data available via foreign keys via getter, so long as 
the getter returns an array of indexable data. In this example, `residences` is 
in a foreign key relationship to Place. The `getResidences` method can return
an array of `Place` objects or an array of strings.

```php
    /**
     * @var Collection|Place[]
     *
     * @ORM\ManyToMany(targetEntity="Place", inversedBy="residents")
     * @ORM\OrderBy({"sortableName": "ASC"})
     *
     * @Solr\Field(type="texts", boost=0.3, getter="getResidences(true)")
     */
    private $residences;
```

The getResidences method will optionally flatten the aliases from Place objects 
to string as in this example below.

```php
    /**
     * @return Collection|array<string>
     */
    public function getResidences(?bool $flat = false) {
        if ($flat) {
            return array_map(fn (Place $p) => $p->getName(), $this->residences->toArray());
        }

        return $this->residences;
    }
```

[doctrine-annotations]: https://www.doctrine-project.org/projects/doctrine-annotations/en/1.13/index.html#introduction
[html_entity_decode]: https://www.php.net/manual/en/function.html-entity-decode.php
