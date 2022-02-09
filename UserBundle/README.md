UserBundle Usage
================

This bundle requires the UtilBundle to work.

Configuration
=============

#### Environment

The User Bundle must be configured with routing information for the site. This
information is used to create cookies and generate URLs in emails. It is required.

```shell
# .env.local

# Routing information
ROUTE_PROTOCOL=http
ROUTE_HOST=localhost
ROUTE_BASE=/nines_demo/public
```

#### Config File

```yaml
# config/packages/nines_user.yaml

nines_user:
    roles: [ ROLE_ADMIN, ROLE_USER_ADMIN, ROLE_BLOG_ADMIN, ROLE_COMMENT_ADMIN, ROLE_CONTENT_ADMIN, ROLE_USER ]
    after_login_route: homepage
    after_request_route: homepage
    after_reset_route: homepage
    after_logout_route: homepage
```

* `roles` is a list of roles a user may be granted. The example includes all 
roles defined in the Nines Bundles. Roles must be defined in `security.yaml`
  (see below) to be useful.
* The four route entries are the names of a route to redirect the user to after
login, requesting a new password, resetting their password, or logging out.

Optionally configure the name of the login cookie in the framework:

#### Security

```yaml
# config/packages/framework.yaml
framework:
    session:
        handler_id: null
        cookie_secure: auto
        cookie_samesite: lax
        name: NU_SESSION # <-- Add and change this line
```

The complete security configuration is below. The configuration includes an opt-in
"remember me" cookie valid for one week, and some default security requirements
for the various bundles. It also puts most of the site behind a login. Remove 
the last two lines of the configuration to open the site.

```yaml
# config/packages/security.yaml
security:
    encoders:
        Nines\UserBundle\Entity\User:
            algorithm: auto

    providers:
        app_user_provider:
            entity:
                class: Nines\UserBundle\Entity\User
                property: email
    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt|error)|css|images|js)/
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
                domain: '%router.request_context.host%'
                name: NU_REMEMBER_ME
                remember_me_parameter: remember_me

    role_hierarchy:
        ROLE_ADMIN: [ ROLE_USER_ADMIN, ROLE_BLOG_ADMIN, ROLE_COMMENT_ADMIN, ROLE_CONTENT_ADMIN, ROLE_USER ]

    # Easy way to control access for large sections of your site
    # Note: Only the *first* access control that matches will be used
    access_control:
        # Default controller stuff - open to the public
        - { path: ^/$, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/privacy$, roles: IS_AUTHENTICATED_ANONYMOUSLY }

        # user controller stuff - open to the public
        - { path: ^/request$, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/reset, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/login$, roles: IS_AUTHENTICATED_ANONYMOUSLY }

        - { path: ^/editor/upload, roles: IS_AUTHENTICATED_ANONYMOUSLY }

        # media bundle
        - { path: ^/audio, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/image, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/pdf, roles: IS_AUTHENTICATED_ANONYMOUSLY }

        # keep the rest of the site private
        - { path: ^/, roles: ROLE_USER }

```

#### Template

Embed the user login/profile/logout menu in your base template:

```twig
{# base.html.twig #}

    {{ knp_menu_render('nines_user_nav') }}
```

#### Console Commands

These shell commands will create an admin user, activate the account, set the 
password and grant the admin role:

```console
$ ./bin/console nines:user:create admin@example.com "Full Name" "Institutional Affiliation"
$ ./bin/console nines:user:activate admin@example.com
$ ./bin/console nines:user:password admin@example.com abc123
$ .//bin/console nines:user:promote admin@example.com ROLE_ADMIN
```

This functionality is also available to admin users through the user menu.
