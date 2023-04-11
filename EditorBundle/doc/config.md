Configuring the Util Bundle
===========================

Configuring the bundle is covered in the main [Nines Bundles](../../README.md) 
documentation. This documentation describes the bundle configuration options.

Requirements
------------

This bundle does not make use of other Nines Bundles.

Configuration Options
--------------------

The configuration options are described below 

```yaml
# config/packages/nines_editor.yaml
nines_editor:
    upload_dir: public/uploads/tinymce
```

* `upload_dir` is the location of files uploaded via the TinyMCE editor.

Editor Configuration
--------------------

The default TinyMCE configuration should be good enough for most purposes. But
you can provide your own by configuring a twig global variable. Copy 
`public/bundles/nineseditor/js/editor-config.js` somewhere in `public` and 
make whatever changes are required.

```twig
# config/packages/twig.yaml
twig:
    ...
    globals:
        nines_editor_config: some/asset/path.js
```
