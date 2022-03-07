Using the Foo Bundle
=====================

Database Tables
---------------

All database table names are prefixed `nines_foo_` to keep them distinct from
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

Forms
-----

Menus
-----

The bundle provides one menu in Menu/Builder.php. It will link to @TODO

```twig
{# templates/base.html.twig #}

    {{ knp_menu_render('nines_foo') }}
```

Templates
---------

Templates are provided in `templates/` and can be 
[easily overridden][override].

[override]: https://symfony.com/doc/current/bundles/override.html#templates
