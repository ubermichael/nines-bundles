Configuring the Util Bundle
===========================

Configure the bundle's parameters.

```yaml
# app/config/parameters.yml and app/config/parameters.yml.dist

    nines_blog.excerpt_length: 100
    nines_blog.homepage_posts: 3
    nines_blog.menu_posts: 5
```

Add the routing information.

```yaml
# app/config/routing.yml

user:
    resource: "@NinesBlogBundle/Resources/config/routing.yml"
```

Configure the Blog bundle.

```yaml
# app/config/config.yml

nines_blog:
    default_status: draft
    default_category: none

nines_user:
    permission_levels: [ ROLE_BLOG_ADMIN ]
```

Add the security configuration. Change it to suit your needs.

```yaml
# app/config/security.yml

security:
    encoders:
        FOS\UserBundle\Model\UserInterface: bcrypt

    role_hierarchy:
        ROLE_ADMIN:       [ ROLE_BLOG_ADMIN ]
        ROLE_BLOG_ADMIN: ROLE_USER
```
