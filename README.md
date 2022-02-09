Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require ubermichael/nines:4.x-dev-up
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundles

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require ubermichael/nines:4.x-dev-up
```

### Step 2: Enable the Bundles You Want

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Nines\BlogBundle\NinesBlogBundle::class => ['all' => true],
    Nines\DublinCoreBundle\NinesDublinCoreBundle::class => ['all' => true],
    Nines\EditorBundle\NinesEditorBundle::class => ['all' => true],
    Nines\FeedbackBundle\NinesFeedbackBundle::class => ['all' => true],
    Nines\MakerBundle\NinesMakerBundle::class => ['dev' => true],
    Nines\MediaBundle\NinesMediaBundle::class => ['all' => true],
    Nines\SolrBundle\NinesSolrBundle::class => ['all' => true],
    Nines\UserBundle\NinesUserBundle::class => ['all' => true],
    Nines\UtilBundle\NinesUtilBundle::class => ['all' => true],
];
```

NinesUtilBundle is required by all the others.

### Step 3: Add Routing Information

Import the routing information as required:

```yaml
# config/routes.yaml
nines_blog:
    resource: '@NinesBlogBundle/config/routes.yaml'
    prefix: blog
    
nines_dublin_core:
    resource: '@NinesDublinCoreBundle/config/routes.yaml'
    prefix: dc

nines_editor:
    resource: '@NinesEditorBundle/config/routes.yaml'
    prefix: editor

nines_feedback:
    resource: '@NinesFeedbackBundle/config/routes.yaml'
    prefix: feedback

nines_media:
    resource: '@NinesMediaBundle/config/routes.yaml'
    prefix: media

nines_user:
    resource: '@NinesUserBundle/config/routes.yaml'
```

### Step 4: Configure the Bundles

Copy the configuration files from the bundles and edit them as required:

```console
$ cp vendor/ubermichael/nines/*/config/nines_*.yaml config/packages/
```

### Step 5: Update Your Database Schema

How you do this one depends on your project and your config. [Doctrine 
migrations](https://symfony.com/bundles/DoctrineMigrationsBundle/current/index.html) are the recommended approach.

### Post Installation

This should be enough to get the bundles working. You may also wish to load the
[Doctrine fixtures](https://symfony.com/bundles/DoctrineFixturesBundle/current/index.html) 
in a development environment.
