Using the Editor Bundle
=====================

Form elements
-------------

A form element with class='tinymce' will automatically be converted to use
the TinyMCE editor.

```php
# src/form/Something.php

        $builder->add('description', TextareaType::class, [
            'label' => 'Description',
            'required' => true,
            'attr' => [
                'help_block' => '',
                'class' => 'tinymce',
            ],
        ]);
```

You can add the editor selectively when a form requires it, or globally so that 
it is available everywhere.

Selective Usage
---------------

Pages with forms that use the TinyMCE editor can include the Javascript library
with the twig block system.

```twig
{# templates/entity/edit.html.twig #}

    {% block javascripts %}
    {% include '@NinesEditor/editor/widget.html.twig' %}
    {% endblock %}
```

Global Usage
------------

Add the widget template in the base template to make it available everywhere. 
This may be undesirable, as it will be available even on pages that do not need 
it.

```twig
{# templates/base.html.twig #}

    {% include '@NinesEditor/editor/widget.html.twig' %}
```
