<?php

namespace Nines\FeedbackBundle\Form;

use Nines\FeedbackBundle\Entity\CommentStatus;
use Nines\UtilBundle\Form\TermType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * CommentStatusType form.
 */
class CommentStatusType extends TermType
{

    /**
     * Define options for the form.
     *
     * Set default, optional, and required options passed to the
     * buildForm() method via the $options parameter.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CommentStatus::class,
        ));
    }

}
