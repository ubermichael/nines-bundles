Using the Maker Bundle
=====================

This bundle provides code for automatically generating controllers, entities, 
and other code. It is highly opinionated and probably won't work well for most.
But you can make any changes you like to suit your opinions.

1. `./bin/console nines:make:entity Poem` will generate a stub Poem entity
in the App namespace. 
2. Add whatever properties the entity requires, along with the necessary doctrine
annotations.
3. `./bin/console make:entity --regenerate App\\Entity\\Poem` to add the setters
and getters. Note that this is _not_ a `nines:` command.
4. `./bin/console nines:make:repo Poem` will generate a Doctrine Repository with
functions for searching and typeahead queries.
5. `./bin/console nines:make:fixtures Poem` to create some poem fixutres with
sensible content.
6. `./bin/console nines:make:form Poem` for a Poem edit/new form.
7. `./bin/console nines:make:controller Poem` for a complete CRUD controller
8. `./bin/console nines:make:template Poem` for the CRUD templates.
9. `./bin/console nines:make:controller-test Poem` will generate a working test 
for the controller
10. `./bin/console nines:make:repo-test Poem` to generate a set of tests for the
repository.

There are some other convenience makers. 

* `./bin/console nines:make:debug Poem` will dump all the Poem metadata to the
console.
* `./bin/console nines:make:index` will create a basic 
[Solr](../../SolrBundle/doc/index.md) Index class.
* `./bin/console nines:make:menu` generates a stub menu.

Templates
---------

Templates are provided in `templates/` and can be 
[easily overridden][override].

[override]: https://symfony.com/doc/current/bundles/override.html#templates
