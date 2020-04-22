Nines Bundles
=============

Some useful bundles.

Instalation
-----------

A Flex recipe isn't included. 

0. Set the environment variables

```text
###> doctrine/doctrine-bundle ###
DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7
###< doctrine/doctrine-bundle ###

###> symfony/mailer ###
MAILER_DSN=null://null
###< symfony/mailer ###

# Routing information
ROUTE_PROTOCOL=http
ROUTE_HOST=localhost
ROUTE_BASE=/path/to/public/index.php
```

1. Add the repository and package to your composer configuration

```json
{
    "require": {
        "ubermichael/nines": "3.x-dev"
    },
    "repositories": [
        {
            "type": "github",
            "url": "https://github.com/ubermichael/nines-bundles.git"
        }
    ],
}
```

And then download them.

```shell
$ composer update ubermichael/nines
```

2. Enable the bundles you need.

```php
// config/bundles.php

return [
    // ...
    Nines\BlogBundle\NinesBlogBundle::class => ['all' => true],
    Nines\DublinCoreBundle\NinesDublinCoreBundle::class => ['all' => true],
    Nines\EditorBundle\NinesEditorBundle::class => ['all' => true],
    Nines\FeedbackBundle\NinesFeedbackBundle::class => ['all' => true],
    Nines\UserBundle\NinesUserBundle::class => ['all' => true],
    Nines\UtilBundle\NinesUtilBundle::class => ['all' => true],
];
```

3. Copy the default configurations to `config/packages`

```shell
$ cp vendor/ubermichael/nines/*/Resources/config/nines_*.yaml config/packages/
```

4. Update the routing information. The examples below are for the default routing.

```yaml
# config/routes.yaml
nines_user:
  resource: '@NinesUserBundle/Resources/config/routes.yaml'

nines_editor:
  type: annotation
  prefix: /editor
  resource: '@NinesEditorBundle/Controller'

nines_blog:
  type: annotation
  prefix: /blog
  resource: '@NinesBlogBundle/Controller'

nines_dc:
  type: annotation
  prefix: /dublin_core
  resource: '@NinesDublinCoreBundle/Controller'

nines_feedback:
  type: annotation
  prefix: /feedback
  resource: '@NinesFeedbackBundle/Controller'
```

5. Update the security configuration with the login settings and role checking. Change
the $APP_REMEMBER_ME variable to something appropriate.

```yaml
# config/packages/security.yaml
security:
    encoders:
        Nines\UserBundle\Entity\User:
            algorithm: auto

    # https://symfony.com/doc/current/security.html#where-do-users-come-from-user-providers
    providers:
        # used to reload user from session & other features (e.g. switch_user)
        app_user_provider:
            entity:
                class: Nines\UserBundle\Entity\User
                property: email
    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        main:
            anonymous: lazy
            guard:
                authenticators:
                    - Nines\UserBundle\Security\LoginFormAuthenticator
            user_checker: Nines\UserBundle\Security\UserChecker
            logout:
                path: nines_user_security_logout
                target: homepage

            remember_me:
                secret: '%kernel.secret%'
                lifetime: 604800 # 1 week
                path: '%router.request_context.base_url%'
                samesite: strict
                httponly: true
                name: $APP_REMEMBER_ME

    role_hierarchy:
        ROLE_ADMIN: [ ROLE_USER_ADMIN, ROLE_BLOG_ADMIN, ROLE_COMMENT_ADMIN, ROLE_DC_ADMIN, ROLE_USER ]

    # Easy way to control access for large sections of your site
    # Note: Only the *first* access control that matches will be used
    access_control:
        - { path: ^/login$, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/dublin_core/element, roles: ROLE_USER }
        - { path: ^/feedback/comment_note, roles: ROLE_USER }
        - { path: ^/feedback/comment_status, roles: ROLE_USER }
```

6. Configure the host information for cookies and password reset emails.

```yaml
# config/services.yaml
parameters:
    router.request_context.scheme: '%env(string:ROUTE_PROTOCOL)%'
    router.request_context.host: '%env(string:ROUTE_HOST)%'
    router.request_context.base_url: '%env(string:ROUTE_BASE)%'

    asset.request_context.base_path: '%env(string:ROUTE_BASE)%'
    asset.request_context.secure: auto
```

7. Add the routing information to the framework settings. Change $APP_NAME to something
appropriate.
```yaml
# config/packages/framework.yaml
framework:
    session:
        name: $APP_NAME
        cookie_lifetime: 0
        cookie_path: '%env(string:ROUTE_BASE)%'
        cookie_domain: '%env(string:ROUTE_HOST)%'
        cookie_samesite: strict
        cookie_secure: auto
```

7. Load the database schema. The example below uses doctrine:schema:update, but a
doctrine migration would be better.

```shell
$ ./bin/console doctrine:schema:update --force
```

8. Optional. Load the data fixtures.
```shell
$ composer require --dev doctrine/doctrine-fixtures-bundle
$ ./bin/console doctrine:fixtures:load -n
```

9. Optional. Add some menus to your base template
```twig
{# templates/base.html.twig #}

{# Optionally change the title from the default. #}
{% set blog_menu = knp_menu_get('nines_blog_pages', [], {'title': 'About'}) %}
{{ knp_menu_render('blog_menu') }}

{# render the menu with the default title #}
{{ knp_menu_render('nines_blog_posts') }}

{# Optionally check for a role before rendering a menu #}
{% if is_granted('ROLE_DC_ADMIN') %}
    {{ knp_menu_render('nines_dc_elements') }}
{% endif %}
{% if is_granted('ROLE_COMMENT_ADMIN') %}
    {{ knp_menu_render('nines_feedback') }}
{% endif %}

{{ knp_menu_render('nines_user_nav') }}
```

10. Optional. Google Font Magic!

Create a fonts configuration and then download the fonts and spit out a scss file. Don't forget to 
add public/fonts and public/sass/_fonts.scss to your .gitignore.

```twig
# config/fonts.yaml

fonts:
  path: public/fonts
  prefix: /pi/
  sass: public/sass/_fonts.scss

  families:
    roboto:
      styles: [ 'normal', 'italic' ]
      weights: [100, 300, 400, 500, 700, 900]

  formats: [ 'woff2', 'woff' ]
  subsets: [ 'latin', 'latin-ext' ]

  filename: "{id}-{weight}.{ext}"

```

```shell script
./bin/console nines:fonts:download
```

If you feel adventurous, you can even add the command to composer.json.

```json
{
    "scripts": {
        "auto-scripts": {
            "cache:clear": "symfony-cmd",
            "nines:fonts:download": "symfony-cmd",
            "assets:install %PUBLIC_DIR%": "symfony-cmd"
        }
    }
}
```
