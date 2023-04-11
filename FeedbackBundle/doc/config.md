Configuring the Util Bundle
===========================

Configuring the bundle is covered in the main [Nines Bundles](../../README.md) 
documentation. This documentation describes the bundle configuration options.

Requirements
------------

This bundle makes use of other Nines Bundles:
* Feedback Bundle to allow commenting on blog pages and posts
* User Bundle to manage user accounts
* Util Bundle for various bits that do not fit in other bundles

Configuration Options
--------------------

The configuration options are described below 

```yaml
# config/packages/nines_feedback.yaml
nines_feedback:
    default_status: submitted
    public_status: published
    subject: New feedback received
    sender: noreply@example.com
    recipients:
        - user@example.com
        - another@example.com
```

- `default_status`: The name of the CommentStatus entity for newly submitted 
comments
- `public_status`: The name of the CommentStatus entity for published comments
- `subject`: The text of the comment notification email subject line
- `sender`: The sender address on the comment notification email
- `recipients`: A list of email addresses to send comment notifications to.

Security Configuration
----------------------

The controllers will only allow users granted `ROLE_FEEDBACK_ADMIN` 
access to create or edit content.

```yaml
# config/packages/security.yaml

security:
    # ...
    role_hierarchy:
        ROLE_ADMIN: [ ROLE_ADMIN, ... , ROLE_FEEDBACK_ADMIN, ... , ROLE_USER ]
```

Include this role in the user bundle configuration, so that it appears in the 
admin user edit form.

```yaml
# config/packages/nines_user.yaml
nines_user:
    roles: [ ROLE_ADMIN, ... , ROLE_FEEDBACK_ADMIN, ... , ROLE_USER ]

```
