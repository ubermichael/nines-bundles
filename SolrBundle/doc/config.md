Configuring the Solr Bundle
===========================

Configuring the bundle is covered in the main [Nines Bundles](../../README.md) 
documentation. This documentation describes the bundle configuration options.

Requirements
------------

This bundle makes use of other Nines Bundles:
* Maker Bundle to create stubs and scaffolding for entities
* Util Bundle for linking search results to entity show pages

Configuration Options
--------------------

### .env file 

Add these lines to an appropriate .env file just as you would when configuring a
doctrine database. We keep solr disabled in the root .env file, and then 
selectively enable it in .env.local, .env.test.local, etc.

In the example below, `nines_demo` is the name of the core. The "URL" isn't
a usable URL for the core, which would be http://localhost:8983/solr/#/nines_demo.

```shell
# Solr Config
SOLR_ENABLED=true
SOLR_URL=http://localhost:8983/nines_demo
```

### nines_solr.yaml

The configuration options are mostly parsed from the .env system. The additional
option, `page_size` is the number of results on a search page. 

```yaml
# config/packages/nines_solr.yaml
nines_solr:
    enabled: '%env(bool:SOLR_ENABLED)%'
    url: '%env(resolve:SOLR_URL)%'
    page_size: 25
```
