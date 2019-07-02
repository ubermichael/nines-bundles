Using the Util Bundle
=====================

Create the database table

```bash
./bin/console doctrine:schema:update --dump-sql
```

Include the routing file

```yaml
# app/config/routing.yml
dc:
    resource: '@NinesDublinCoreBundle/Resources/config/routing.yml'
```

Display the menu somewhere

```twig
   {{ knp_menu_render('dc') }}
```

Load data into the elements table by visiting element/new or
by loading the fixtures in Nines\DublinCoreBundle\DataFixtures\ORM\LoadElement.php

