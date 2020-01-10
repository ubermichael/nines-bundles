Installation
============

Step 1: Download the package
----------------------------

Open your composer.json file and add the Nines Bundles repository.

```json
    "repositories": [
        {
            "type": "github",
            "url": "https://github.com/ubermichael/nines-bundles.git"
        }
    ],

```

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require ubermichael/nines 1.x-dev
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project. The Nines Bundles package
contains more bundles than this one.

```php
<?php 

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [

            // ...
            new Nines\EditorBundle\NinesEditorBundle(),
        ];

        // ...
    }

    // ...
}
```

Step 3: Configure the Bundle
----------------------------

See [Configuration](config.md)
