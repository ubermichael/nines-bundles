Using the Util Bundle
=====================

Your application's Entities can extend Nines\UtilBundle\AbstractEntity, which provides an
ID attribute and created/updated fields that are automatically maintained.

```php
<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * Post
 *
 * @ORM\Table(name="post")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PostRepository")
 */
class Post extends AbstractEntity
{

    // No $id necessary here.

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $title;

     /**
      * @var Category
      * @ORM\ManyToOne(inversedBy="posts", targetEntity="Category")
      */
     private $category;
 
   /**
     * Force all entities to provide a stringify function.
     *
     * @return string
     */
    public function __toString() {
        return $this->title;
    }
    
    // etc.
}
```

For simple lookup tables extend the AbstractTerm entity. This class definition provides
a computer-readable name, a human readable label, and an optional description.

```php
<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractTerm;

/**
 * Category
 *
 * @ORM\Table(name="category")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CategoryRepository")
 */
class Category extends AbstractTerm
{

    /**
     * @var Collection|Post[]
     * @ORM\OneToMany(mappedBy="category", targetEntity="Post")
     */
    private $posts;

    public function __construct() {
        parent::__construct();
        $this->posts = new ArrayCollection();
    }
    
}
```

The bundle also defines a form for easy reuse.

```php
<?php

namespace AppBundle\Form;

use Nines\UtilBundle\Form\TermType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * CategoryType form.
 */
class CategoryType extends TermType
{
    /**
     * Add form fields to $builder.
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // customize if necessary.
        parent::buildForm($builder,$options);
    }
    
    // ...

}
```

And the bundle defines a few templates that can be reused or 
extended as needed.

```twig
{# src/AppBundle/Resources/views/index.html.twig #}

{% embed '@NinesUtilBundle/term/partial/index.html.twig' 
    with {'terms': categories, 'path': 'category_show' } %}
{% endembed %}
```

```twig
{# src/AppBundle/Resources/views/show.html.twig #}

{% embed '@NinesUtilBundle/term/partial/show.html.twig' 
    with {'term': category} %}
{% endembed %}

```
