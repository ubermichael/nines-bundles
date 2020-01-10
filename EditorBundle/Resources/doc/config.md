Configuring the Util Bundle
===========================

Configure the image upload directory.

```yaml
# app/config/parameters.yml and app/config/parameters.yml.dist

parameters:
    nines.editor.upload_dir: web/tinymce
```

Add the routing information to the app config.

```yaml
# app/config/routing.yml

editor:
    resource: '@NinesEditorBundle/Resources/config/routing.yml'
```
