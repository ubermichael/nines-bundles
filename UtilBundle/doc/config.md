Configuring the Util Bundle
===========================

Configuring the bundle is covered in the main [Nines Bundles](../../README.md) 
documentation. This documentation describes the bundle configuration options.

Requirements
------------

This bundle is used by all other Nines Bundles. It does not use them itself.

Configuration Options
--------------------

The configuration options are described below.

```yaml
# config/packages/nines_Util.yaml
nines_util:
    trim_length: 50
    sender: 'donotreply@%router.request_context.host%'
    routing:
        - { class: Nines\BlogBundle\Entity\Post, route: nines_blog_post_show }
        - { class: App\Entity\Poem, route: poem_show }
```

- `trim_length` is probably unused and should probably be removed.
- `sender` is the From: addresss to use when sending emails.
- `routing` is an associative array for linking entity classes to a controller 
action to display details about the entity.

The Util bundle also provides an optional error handling controller which can
be enabled in the framework.

```yaml
# config/packages/framework.yaml
framework:
    error_controller: Nines\UtilBundle\Controller\ErrorController::show
```

The bundle also provides a number of services and templates for Twig. The 
relevant configuration options are shown below.

```twig
# config/packages/twig.yaml
twig:
    form_themes:
        - "@NinesUtil/form/fields.html.twig"
    globals:
        matomo_enabled: '%dhil.matomo_enabled%'
        matomo_url: '%dhil.matomo_url%'
        matomo_siteid: '%dhil.matomo_siteid%'
        matomo_domain: '%dhil.matomo_domain%'
        linker: '@Nines\UtilBundle\Services\EntityLinker'
```

The `dhil.*` parameters are defined in the services configuration and are only 
necessary if you embed the matomo.html.twig template in your base template.

```yaml
# config/services.yaml
parameters:
    dhil.matomo_enabled: '%env(bool:MATOMO_ENABLED)%'
    dhil.matomo_url: ~
    dhil.matomo_siteid: ~
    dhil.matomo_domain: ~
```
