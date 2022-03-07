Configuring the Util Bundle
===========================

Configuring the bundle is covered in the main [Nines Bundles](../../README.md) 
documentation. This documentation describes the bundle configuration options.

Requirements
------------

This bundle makes use of other Nines Bundles:
* Blog Bundle to publish announcements and documentation
* Dublin Core Bundle to attach metadata to entities
* Editor Bundle to provide a WYSIWYG editing interface
* Feedback Bundle to allow commenting on blog pages and posts
* Maker Bundle to create stubs and scaffolding for entities
* Media Bundle for handling image, audio, and PDF uploads
* Solr Bundle for indexing entities and providing search functionality
* User Bundle to manage user accounts
* Util Bundle for various bits that do not fit in other bundles

Configuration Options
--------------------

The configuration options are described below 

```yaml
# config/packages/nines_foo.yaml
nines_foo:
```

Security Configuration
----------------------

The controllers will only allow users granted `ROLE_FOO_ADMIN` 
access to create or edit content.

```yaml
# config/packages/security.yaml

security:
    # ...
    role_hierarchy:
        ROLE_ADMIN: [ ROLE_ADMIN, ... , ROLE_FOO_ADMIN, ... , ROLE_USER ]
```

Include this role in the user bundle configuration, so that it appears in the 
admin user edit form.

```yaml
# config/packages/nines_user.yaml
nines_user:
    roles: [ ROLE_ADMIN, ... , ROLE_FOO_ADMIN, ... , ROLE_USER ]

```
