Using the Feedback Bundle
=====================

Database Tables
---------------

All database table names are prefixed `nines_feedback_` to keep them distinct from
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

Comments are stored in Comment entities. They can have CommentNotes associated
with them (eg. "I followed up with the submitter" or "Not sure if this is spam.")
A comment status is associated with each comment, so that workflows can be built.

Menus
-----

The bundle provides one menu in Menu/Builder.php. It will link to @TODO

```twig
{# templates/base.html.twig #}

    {{ knp_menu_render('nines_feedback') }}
```

Templates
---------

Templates are provided in `templates/` and can be
[easily overridden][override].

[override]: https://symfony.com/doc/current/bundles/override.html#templates

How To Use
==========

First configure the Nines Util bundle to generate URLs for the entities with
comments.

```yaml
# config/packages/nines_util.yaml
nines_util:
    ...
    routing:
        - { class: 'App\Entity\Book', route: book_show }
        - { class: 'App\Entity\Document', route: document_show }
```

Next add the Comment UI partial template to the entity's show.html.twig page.

```twig
{# templates/poem/show.html.twig #}

    ...
    {% include '@NinesFeedback/comment/comment-interface.html.twig' with { 
        'entity': book 
    } %}
```

Finally, make sure the [Symfony mail system][mail] is set up correctly.

[mail]: https://symfony.com/doc/current/mailer.html
