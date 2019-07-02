Configuring the Util Bundle
===========================

Add the form theme, templates, and text service to Twig. 

```yaml
twig:
    form_themes:
        - '@NinesUtilBundle/form/fields.html.twig'
    paths:
        '%kernel.project_dir%/vendor/ubermichael/Nines/UtilBundle/Resources/views/': NinesUtilBundle
    globals:
        text_service: '@Nines\UtilBundle\Services\Text'
```
