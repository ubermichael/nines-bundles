Nines Editor Bundle
===================

A simple Symfony wrapper around TinyMCE.

Installation
------------

### Step 1: Download the package

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
$ composer require ubermichael/nines 2.x-dev
   ```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Configure it

Add the image upload directory parameter.

```yaml
# app/config/paramters.yml and app/config/paramters.yml.dist

paramters:
    nines.editor.upload_dir: web/tinymce
```

Make sure the web server can write to the directory.

### Step 3: Forms

Add the ``tinymce`` class to the form widgets that will be WISIWYGish.

```php

# AppBundle/Forms/ContentType.php

    $builder->add('content', null, array(
            'label' => 'Content',
            'required' => false,
            'attr' => array(
                'help_block' => '',
                'class' => 'tinymce',
            ),
        ));
```

Then add the editor widget javascript to your templates.

```twig

{# AppBundle/Resources/views/content/new.html.twig #}

{% block javascripts %}
    {% include 'NinesEditorBundle:editor:widget.html.twig' %}
{% endblock %}

```

### Custom Config

To customize the editor configuration, copy Resources/public/js/editor-config.js to 
your bundle and make whatever changes you need. Then add the asset path to the twig 
global variable configuration.

```yaml
# app/config/config.yml

twig:
    globals:
      nines_editor_config: bundles/nineseditor/js/editor-config.js

```
