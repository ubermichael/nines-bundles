Using the Util Bundle
=====================

Configure bower or yarn or npm or something for the project. and add TinyMCE as a 
dependency. How you do that is up to you. The instructions below assume you 
used Bower. 

Add jQuery to your base template.

```twig
# app/Resources/views/base.html.twig

        <script src="{{ asset('bower/jquery/dist/jquery.min.js') }}"></script>
```

Add the javascript assets to a form template or to the base template if you 
like to live dangerously.

```twig

{% block javascripts %}
    {% include 'NinesEditorBundle:editor:widget.html.twig' %}
{% endblock %}

```

Any text area form widget on the page should now be a TinyMCE WYSIWYG editor. And 
image uploads should work.
