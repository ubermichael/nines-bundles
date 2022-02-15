UtilBundle Usage
================

Implement `AbstractEntityInterface` and extend `AbstractEntity` for automatically
generated IDs and timestamps.

Extend the `AbstractTerm` class for handly lookup tables. Forms based on 
`AbstractTerm`s can extend the `TermType` form and call it's `buildForm` method 
to add the label and description fields. Templates for `AbstractTerm`s can embed
the provided partial templates `index.html.twig`, `search.html.twig`, and 
`show.html.twig`. The templates include a `callback` block for extensibility.

```twig
    {% embed '@NinesUtil/term/partial/index.html.twig' with {
        'terms': categories,
        'path': 'category_show',
    } %}
    {% endembed %}
```

Database repository classes for `AbstractTerm` classes can extend the 
`TermRepository` to reduce code duplication.

The `ContentEntityInterface` and `ContentEntityTrait` add fields for content
and an automatically generated excerpt. A Doctrine listener will generate the 
excerpt when saving to the database.

Track user contributions to entities with the `ContributorInterface` and 
`ContributorTrait`. A Doctrine listener will add the contributor name and date
to a field in the entity.

* @TODO: Provide a partial for the comments.

The code should make use of foreign keys to describe entity relationships. But that
isn't always possible (you may not know which entities are related). For that
situation the `LinkedEntityInterface` and `LinkedEntityTrait` are useful.

Extend the `AbstractBuilder` class to get some convienent Bootstrap 3 menuing
functions. See `DublinCoreBundle/Menu/Builder.php` for a simple example. There's 
a more complex example in the BlogBundle.

Services
--------

* `ContributionManager` is a doctrine listener that adds usernames and date
stamps to `ContributionInterface` derived classes.
* `EntityLinker` manages links between entities where foreign key relationships
are difficult or impossible to describe.
* `Notifier` sends email notifications.
* `Text` and `TitleCaser` provide some text processing utilities.

Test Utilities
--------------

* `ServiceTestCase` is a very simple extension of Symfony's `KernelTestCase` that
provides an entity manager for subtests and configures the testing environment.
* `CommandTestCase` extends `ServiceTestCase` and include a function to execute 
Symfony commands in a test environment and collect the result.
* `ControllerTestCase` extends Symfony's WebTestCase and provides a method to 
simulate a login, reset or commit any database changes, and dump the result
of the most recent client http request.


Configuration
=============

The bundle provides a very minimal configuration.

```yaml
# config/packages/nines_util.yaml

nines_util:
    trim_length: 50
    sender: 'donotreply@%router.request_context.host%'
    routing:
        - { class: App\Entity\Foo, route: foo_show }
```

* `trim_length`: Excerpts will be limited to 50 words
* `sender`: Notification emails will be sent from this address
* `routing`: Entities can be automatically linked via the EntityLinker service, 
but they must be configured here for it to work.

### Error Handler

There is an error controller which provides nicer error messages than Symfony
does. Configure the framework to use it like so:

```yaml
# config/packages/framework.yaml

    ...
    error_controller: Nines\UtilBundle\Controller\ErrorController::show
```

### Entity Linker

```yaml
# config/packages/twig.yaml

twig:
    ...
    globals:
        ...
        linker: '@Nines\UtilBundle\Services\EntityLinker'
```

### Logging
Util bundle includes a Monolog formatter which adds URL and IP information to
log messages. Enable it by adding it to the monolog config like so:

```yaml
# config/packages/dev/monolog.yaml

monolog:
    handlers:
        main:
            [...]
            formatter: nines.formatter.request

```

Messages in the dev log will look like this:

```
[2022-02-15 18:41:40] [request.ERROR] [127.0.0.1] [http://localhost/nines_demo/public/asdf]
Uncaught PHP Exception Symfony\Component\HttpKernel\Exception\NotFoundHttpException: "No route found for "GET /asdf"" at /Users/michael/Sites/nines_demo/vendor/symfony/http-kernel/EventListener/RouterListener.php line 136
```
