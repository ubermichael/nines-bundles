Using the Solr Bundle
=====================

Solr is a complex system and integrating it into a Symfony application is not
simple. There are many steps.

1. [Configure](config.md) the bundle
2. Add annotations and metadata to the entities to be indexed
3. Index the entities
4. Create search forms
5. Add solr search routes and controllers
6. Add templates for searching

Entities
--------

Solr Bundle's annotations are modelled after the annotations in the Doctrine 
ORM, and will hopefully seem familiar. 

Add the `@Solr\Document` annotation to each entity to be indexed and put
an `@Solr\Id` annotation on the unique entity identifier. Add the `@Solr\Field` 
annotation to each non-ID field to be indexed.

See the [annotations](annotations.md) documentation for details about each of the
annotations and the attributes they understand.

Commands
--------

This bundle provides commands to debug the annotations, ping the server, and 
interact with the core.

### Debug

`nines:solr:schema` will output information about which entities will be indexed
and how. In the example below `App\Entity\Ttle` is an entity that will be 
indexed. The entity identifier is accessed by the `getId` method. The solr 
field `title_txt` is constructed from the solr fields `main_t`, and `sub_t`.

The display also shows the other fields and infomration about how they are
constructed, mutated, or filtered. See [annotations](annotations.md) for details.

```console
$ ./bin/console nines:solr:schema
App\Entity\Title
  id => App\Entity\Title:getId
  type_s => Title
  title_txt <= [main_t,sub_t]
  content_txt <= [main_t,sub_t,description_t]
+-------------------+-------------+-----------------+---------+--------------------+
| name              | field       | getter          | mutator | filters            |
+-------------------+-------------+-----------------+---------+--------------------+
| main_t            | main        | getMain         |         |                    |
| sub_t             | sub         | getSub          |         |                    |
| price_f           | price       | getPrice        |         |                    |
| description_t^0.5 | description | getDescription  |         | strip_tags         |
|                   |             |                 |         | html_entity_decode |
| created_dt        | created     | getCreated      | format  |                    |
| updated_dt        | updated     | getUpdated      | format  |                    |
| tax_price_f       | tax_price   | getPriceWithTax |         |                    |
+-------------------+-------------+-----------------+---------+--------------------+
```

`nines:solr:dump <class> <id>` will show how one instance of an entity would be
indexed in the Solr core.

In this example Title #1 would be indexed with these fields and values.

```console
$ ./bin/console nines:solr:dump Title 1
+---------------+----------------------+
| Field         | Value                |
+---------------+----------------------+
| id            | App\Entity\Title:1   |
| class_s       | App\Entity\Title     |
| type_s        | Title                |
| main_t        | Main 1               |
| sub_t         | Sub 1                |
| price_f       | 1                    |
| description_t | This is paragraph 1  |
| created_dt    | 2022-02-15T20:24:27Z |
| updated_dt    | 2022-02-15T20:24:27Z |
| tax_price_f   | 1.05                 |
| title_txt     | Main 1               |
|               | Sub 1                |
| content_txt   | Main 1               |
|               | Sub 1                |
|               | This is paragraph 1  |
+---------------+----------------------+
```

### Ping

Check that the bundle is configured and the server is running and reachable 
with `nines:solr:ping`.

```console
$ ./bin/console nines:solr:ping
Solarium library version: 6.2.3
200 OK
Ping: 37ms
```

### Server Commands

Remove all content from the Solr core with `nines:solr:clear`. This command does
not produce output unless there is an error.

```console
$ ./bin/console nines:solr:clear
```

Index (or reindex) content with `nines:solr:index`. 

```console
$ ./bin/console nines:solr:index
App\Entity\Title
 5/5 [============================] 100%
```

This command accepts the option `--clear` which will remove all content from 
the index first. Without this option, Solr will replace all content with new
content. Indexed data that does not correspond to something in the database will 
not be changed (eg. an entity was removed form the database but not the index 
will remain).

You can also choose which entities to index by passing the entity class name on
the command line. The command assumes a namespace prefix of `\App\Entity` unless 
you provide a fully-qualified class name. Note the of the backslashes
due to shell escaping rules.

Also note that `--clear` will remove all content from the Solr core, not the data
associated with the class name passed as an argument.

```console
$ ./bin/console nines:solr:index --clear Title
App\Entity\Title
5/5 [============================] 100%

$ ./bin/console nines:solr:index --clear App\\Enitty\\Title
App\Entity\Title
5/5 [============================] 100%
```

Forms
-----

Form types are not provided for this bundle. Instead, some suggested twig 
partial templates are provided, which can be used directly, 
[overridden][override], or ignored as needed. The forms assume a 
[Bootstrap 3][bootstrap] base template and CSS.

`search_field.html.twig` renders a single text input with search and reset 
buttons. It expects the path to the search in the `path` template variable.

```twig
<div class='row'>
    {% embed '@NinesSolr/search/partial/search_field.html.twig' with {'path': 'title_search'} %}
    {% endembed %}
</div>
```

![Rendered search field](img/search_field.png)

`order.html.twig` provides a dropdown to select the order of search results. It 
expects two parameters. `header` is the name of the section in the sidebar. 
`options` is a key, value array. The keys are strings of the form
`field`.`direction` and the values are the entries in the dropdown menu.

```twig
<div class='row'>
    <div class='col-sm-3'>
        {% if result %}
            {% include '@NinesSolr/search/partial/order.html.twig' with {
                'header': 'Result Order',
                'options': {
                    'score.desc': 'Relevance',
                    'main.asc': 'Title (A to Z)',
                    'main.desc': 'Title (Z to A)',
                    'price.asc': 'Price (Low to High)',
                    'price.desc': 'Price (High to Low)',
                }
            } %}
        {% endif %}
    </div>
</div>
```

![Rendered options](img/order.png)

`facet.html.twig` and `facet_range.html.twig` provide facets/filtering for 
search results. Facets are defined in the [index classes](index_classes.md). 

```twig
    {% include '@NinesSolr/search/partial/facet_range.html.twig' with {
        'facet': result.getFacet('price'),
        'filter': 'price',
        'header': 'Price',
    } %}
```

![Rendered facet](img/facets.png)

Indexes
-------



Templates
---------

Templates are provided in `templates/` and can be 
[easily overridden][override].

[override]: https://symfony.com/doc/current/bundles/override.html#templates
[bootstrap]: https://getbootstrap.com/docs/3.4/
