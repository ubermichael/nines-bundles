Nines Bundles
=============

Some useful bundles.

Instalation
-----------

A Flex recipe isn't included. All steps are required. 

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

5. Update the security configuration with the role checking.

```yaml
# config/packages/security.yaml
    role_hierarchy:
        ROLE_ADMIN: 
          - ROLE_USER_ADMIN
          - ROLE_BLOG_ADMIN
          - ROLE_COMMENT_ADMIN
          - ROLE_DC_ADMIN

    # Easy way to control access for large sections of your site
    # Note: Only the *first* access control that matches will be used
    access_control:
        - { path: ^/, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/login$, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/logout, roles: ROLE_USER }
```

