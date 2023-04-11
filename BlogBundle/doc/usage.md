Using the Blog Bundle
=====================

Database Tables
---------------

All database table names are prefixed `nines_blog_` to keep them distinct from
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

They can be loaded as in this example, which should be used with caution:

```shell
$ ./bin/console doctrine:fixtures:load --group=prod
```

Entities
--------

There are four entities defined in the bundle: pages, posts, categories, and
statuses.

Pages are meant to for long-lasting content like "About" pages, documentation
or acknowledgements. Posts are more blog like. They are intended for timely
announcements. Categories aid in organizing the pages, and can be used to build
menus (some coding required). Statuses are used to define the workflow. Some
instances might require draft and published statuses, while others can create
more complex workflows involving editorial steps.

Menus
-----

The bundle provides two menus in Menu/Builder.php which are configured as 
Symfony services. Add the pages and posts menus to a base template or provide
your own menus.

```twig
{# templates/base.html.twig #}

    {{ knp_menu_render('nines_blog_pages') }}
    {{ knp_menu_render('nines_blog_posts') }}
```

Templates
---------

Templates are provided in `templates/` and can be 
[easily overridden](https://symfony.com/doc/current/bundles/override.html#templates).

