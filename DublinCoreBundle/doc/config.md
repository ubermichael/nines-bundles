Configuring the Util Bundle
===========================

Configuring the bundle is covered in the main [Nines Bundles](../README.md) 
documentation. This documentation describes the bundle configuration options.

Requirements
------------

This bundle makes use of other Nines Bundles:
* User Bundle to manage user accounts
* Util Bundle for various bits that do not fit in other bundles

Configuration Options
--------------------

There are no direct configuration options for this bundle. 

```yaml
# config/packages/nines_dublin_core.yaml
nines_dublin_core:
```


Security Configuration
----------------------

The controllers will only allow users granted `ROLE_DC_ADMIN` 
access to create or edit content.

```yaml
# config/packages/security.yaml

security:
    # ...
    role_hierarchy:
        ROLE_ADMIN: [ ROLE_ADMIN, ... , ROLE_DC_ADMIN, ... , ROLE_USER ]

```

Include this role in the user bundle configuration, so that it appears in the 
admin user edit form.

```yaml
# config/packages/nines_user.yaml
nines_user:
    roles: [ ROLE_ADMIN, ... , ROLE_DC_ADMIN, ... , ROLE_USER ]

```
