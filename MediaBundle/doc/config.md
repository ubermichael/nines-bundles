Configuring the Util Bundle
===========================

Configuring the bundle is covered in the main [Nines Bundles](../../README.md) 
documentation. This documentation describes the bundle configuration options.

Requirements
------------

This bundle makes use of other Nines Bundles:
* Editor Bundle to provide a WYSIWYG editing interface
* User Bundle to manage user accounts
* Util Bundle for various bits that do not fit in other bundles

In addition, this bundle uses [ImageMagic][imagemagick] via the [imagick][imagick] PECL extension.

Configuration Options
--------------------

The configuration options are described below 

```yaml
# config/packages/nines_media.yaml
nines_media:
    root: 'data'
    thumb_width: 450
    thumb_height: 350
```

* `root` is the directory where uploaded media files and their thumbnails will
be stored. It is either an absolute path or relative to the symfony root. Files
in this directory are divided up by environment. `data/test` will store files
uploading during testing, for example.
* `thumb_width` and `thumb_height` are the dimensions of the generated 
thumbnails.

Security Configuration
----------------------

The controllers will only allow users granted `ROLE_MEDIA_ADMIN` 
access to create or edit content.

```yaml
# config/packages/security.yaml

security:
    # ...
    role_hierarchy:
        ROLE_ADMIN: [ ROLE_ADMIN, ... , ROLE_MEDIA_ADMIN, ... , ROLE_USER ]
```

Include this role in the user bundle configuration, so that it appears in the 
admin user edit form.

```yaml
# config/packages/nines_user.yaml
nines_user:
    roles: [ ROLE_ADMIN, ... , ROLE_MEDIA_ADMIN, ... , ROLE_USER ]

```

[imagemagick]: https://imagemagick.org/index.php
[imagick]: https://pecl.php.net/package/imagick
