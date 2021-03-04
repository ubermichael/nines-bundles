<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\FeedbackBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Comment form.
 */
class AdminCommentType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        $builder->add('fullname');  // string
        $builder->add('email');  // string
        $builder->add('followUp');  // boolean
        $builder->add('content', TextareaType::class, [
            'attr' => [
                'class' => 'tinymce',
            ],
        ]);  // string
        $builder->add('status');
        $builder->setMethod('POST');
    }

    public function configureOptions(OptionsResolver $resolver) : void {
        $resolver->setDefaults([
            'data_class' => 'Nines\FeedbackBundle\Entity\Comment',
        ]);
    }
}
