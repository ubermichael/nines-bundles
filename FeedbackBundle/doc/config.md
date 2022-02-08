Configuring the Feedabck Bundle
===============================

Import routing for the bundle

```yaml
# app/config/routing.yml

feedback:
    resource: '@NinesFeedbackBundle/Resources/config/routing.yml'
```

Define the comment notification parameters.

```yaml
# app/config/parameters.yml.dist
    # comment notifications
    nines_feedback.sender: ~
    nines_feedback.recipient: ~
    nines_feedback.subject: New feedback received
```

Add the templating engine to the framework, configure the feedback bundle 
to map entities to routes, add the comment service global variable to twig, 
and which comments should be published. 


```yaml
# app/config/config.yml

framework:
    #esi: ~
    #translator: { fallbacks: ['%locale%'] }
    templating:
        engines:
            - twig

twig:
    globals:
        comment_service: '@Nines\FeedbackBundle\Services\CommentService'


nines_feedback:
    default_status: submitted
    public_status: published
    commenting:
        post:
            class: AppBundle\Entity\Post
            route: post_show

```

Add the ROLE_COMMENT_ADMIN to the user and security config.

```yaml
# app/config/config.yml
nines_user:
    permission_levels: [ ROLE_ADMIN, ROLE_COMMENT_ADMIN ]
```

```yaml
# app/config/security.yml

security:
    role_hierarchy:
      ROLE_ADMIN: [ ROLE_COMMENT_ADMIN ]
```
