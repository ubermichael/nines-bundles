Configuring the Util Bundle
===========================

Configuring the bundle is covered in the main [Nines Bundles](../README.md) 
documentation. This documentation describes the bundle configuration options.

Requirements
------------

This bundle makes use of other Nines Bundles:
* Editor Bundle to provide a WYSIWYG editing interface
* Feedback Bundle to allow commenting on blog pages and posts
* User Bundle to manage user accounts
* Util Bundle for various bits that do not fit in other bundles

Configuration Options
--------------------

```yaml
# config/packages/nines_blog.yaml
nines_blog:
    excerpt_length: 100
    homepage_posts: 3
    menu_posts: 5
    default_status: draft
    default_category: post
```

The configuration options are:

- `excerpt_length` The number of words to show in a content excerpt
- `homepage_posts` If you use the automatically generated [home page](@TODO) content, 
this many posts will be shown on the home page.
- `menu_posts` Generate this many menu items in the [menu generator](@TODO)
- `default_status` Name of the default status for a post
- `default_category` Name of the default page category

Security Configuration
----------------------

In addition, the controllers will only allow users granted ROLE_BLOG_ADMIN 
access to create or edit content.

```yaml
# config/packages/security.yaml

security:
    # ...
    role_hierarchy:
        ROLE_ADMIN: [ ROLE_ADMIN, ROLE_BLOG_ADMIN, ... , ROLE_USER ]

```

Include this role in the user bundle configuration, so that it appears in the
admin user edit form.

```yaml
# config/packages/nines_user.yaml
nines_user:
    roles: [ ROLE_ADMIN, ... , ROLE_DC_ADMIN, ... , ROLE_USER ]

```
