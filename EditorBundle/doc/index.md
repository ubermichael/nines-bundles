Nines Editor Bundle
=================

The Nines Editor Bundle provides a simple wrapper around the [TinyMCE][tinymce] 
editor widget, version 5.

Installation
------------
See the main [Nines Bundles documentation](../../README.md) for installation 
instructions.

Assets
------

This bundle assumes that the CKEditor Javascript and CSS files are available 
in `public/yarn/tinymce`. One way to get them there is to configure [Yarn][yarn]
to store them there, and then add them add tinymce as a yarn package dependency.

```editorconfig
# .yarnrc
--modules-folder public/yarn
```

```shell
$ yarn add tinymce
```

Configuration
-------------

How to configure a Nines Bundle is covered in the main [Nines Bundles](../../README.md)
documentation. [Configuration](config.md) describes the bundle configuration options.

Usage
-----

[Usage](usage.md) describes the layout of the bundle and how to make good use of it.

[tinymce]: [https://www.tiny.cloud]
[Yarn]: [https://yarnpkg.com]
