<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\BlogBundle\Form;

use Nines\BlogBundle\Entity\PostCategory;
use Nines\UtilBundle\Form\TermType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * PostCategoryType form.
 */
class PostCategoryType extends TermType {
    /**
     * Define options for the form.
     */
    public function configureOptions(OptionsResolver $resolver) : void {
        $resolver->setDefaults([
            'data_class' => PostCategory::class,
        ]);
    }
}
