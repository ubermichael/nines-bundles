<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\FeedbackBundle\Form;

use Nines\FeedbackBundle\Entity\CommentStatus;
use Nines\UtilBundle\Form\TermType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * CommentStatusType form.
 */
class CommentStatusType extends TermType {
    /**
     * Define options for the form.
     *
     * Set default, optional, and required options passed to the
     * buildForm() method via the $options parameter.
     */
    public function configureOptions(OptionsResolver $resolver) : void {
        $resolver->setDefaults([
            'data_class' => CommentStatus::class,
        ]);
    }
}
