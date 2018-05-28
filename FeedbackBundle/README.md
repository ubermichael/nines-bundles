Feedback & Commenting Bundle
============================

The Feedback bundle provides very simple commenting for Symfony 
applications.

Installation
============

Download the bundle and put it somewhere Symfony will find it. Add it
to the application kernel and add routing for the bundle.

Configuration
=============

The bundle needs to know how to map entity classes to urls. Add something
like the following to app/config/config.yml:

```yaml
twig:
    # Make the comment service available everywhere in Twig.
    globals:
        comment_service: "@feedback.comment"

feedback:
    commenting:
        author:
            class: AppBundle\Entity\Author
            route: admin_author_show
        alias:
            class: AppBundle\Entity\Alias
            route: admin_alias_show
        publication:
            class: AppBundle\Entity\Publication
            route: admin_publication_show
        place:
            class: AppBundle\Entity\Place
            route: admin_place_show
```

Usage
=====

Add commenting to a twig template:

```twig
    {% include 'NinesFeedbackBundle:comment:comments-interface.html.twig' with {'entity': entity } %}                            
```

This line will show the comment form and public, published comments. If the user
has ROLE_ADMIN privileges, the private comments will also be shown with some 
links to edit them as well.