Using the Feedback Bundle
=========================

Include the commenting template on a configured entity's show page. This 
partial template will show the comment form, list of approved comments, and for
users with ROLE_COMMENT_ADMIN, a list of unapproved comments.

```twig

{% include '@NinesFeedback/comment/comment-interface.html.twig' with {'entity': post} %}
```

Include a comment navigation menu somewhere.

```twig
{% if is_granted('ROLE_COMMENT_ADMIN') %}
    {{ knp_menu_render('feedback') }}
{% endif %}
```

Set up the comment status entries. They should match whatever you configured in 
the nines_feedback part of the [configuration instructions](config.md). Navigate
to admin/comment_status and create the entries.

```yaml
# app/config/config.yml

nines_feedback:
    default_status: submitted
    public_status: published
```
