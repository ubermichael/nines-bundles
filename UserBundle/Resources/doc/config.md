Configuring the Util Bundle
===========================

Configure the bundle's parameters.

```yaml
# app/config/parameters.yml and app/config/parameters.yml.dist
    # make sure the symfony login cookies work with multiple apps on
    # a single domain.
    router.request_context.host: ~
    router.request_context.scheme: http
    router.request_context.base_url: ~
    secure_cookies: false
```

Add the routing information.

```yaml
# app/config/routing.yml

user:
    resource: "@NinesUserBundle/Resources/config/routing.yml"
```

Configure the bundle by enabling the translator, even if it won't be used and 
the templating engine.

```yaml
# app/config/config.yml

framework:
    translator:
        enabled: true
    templating:
        engines: ['twig']
```

Optionally configure the session information. Change APPNAME to suit.

```yaml
# app/config/config.yml

framework:
    session:
        # http://symfony.com/doc/current/reference/configuration/framework.html#handler-id
        handler_id:  session.handler.native_file
        save_path:   ~
        name: PHP_SESSION_APPNAME
        cookie_path: '%router.request_context.base_url%'
        cookie_domain: '%router.request_context.host%'
        cookie_secure: '%secure_cookies%'

```

Configure the FOS User Bundle. It's the parent of the Nines User Bundle.

```yaml
# app/config/config.yml

fos_user:
    db_driver: orm
    firewall_name: main
    user_class: Nines\UserBundle\Entity\User
    profile:
        form:
            type: Nines\UserBundle\Form\ProfileType
    change_password:
        form:
            type: Nines\UserBundle\Form\PasswordType
    from_email:
        address: 'noreply@%router.request_context.host%'
        sender_name: User Manager

nines_user:
    permission_levels: [ ROLE_ADMIN, ROLE_CONTENT_ADMIN ]
```

Add the security configuration. Change it to suit your needs.

```yaml
# app/config/security.yml

security:
    encoders:
        FOS\UserBundle\Model\UserInterface: bcrypt

    role_hierarchy:
        ROLE_ADMIN:       [ ROLE_ADMIN, ROLE_CONTENT_ADMIN ]
        ROLE_BLOG_ADMIN: ROLE_USER
        ROLE_CONTENT_ADMIN: ROLE_USER

    providers:
        fos_userbundle:
            id: fos_user.user_provider.username

    firewalls:
        main:
            pattern: ^/
            form_login:
                provider: fos_userbundle
                csrf_token_generator: security.csrf.token_manager
            logout_on_user_change: true

            # Optional configuration for a remember me check box on login.
            remember_me:
                secret: '%secret%'
                lifetime: 2419200 # 28 days in seconds.
                
                # Optional config
                path: '%router.request_context.base_url%'
                domain: '%router.request_context.host%'
                secure: '%secure_cookies%'

            logout:       true
            anonymous:    true

    # These are the defaults. Adjust as necessary.
    access_control:
        - { path: ^/login$, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/register, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/resetting, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/feedback, roles: [ ROLE_USER, IS_AUTHENTICATED_ANONYMOUSLY ]}
        - { path: ^/admin/, role: ROLE_ADMIN }
```
