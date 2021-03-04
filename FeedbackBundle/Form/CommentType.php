<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\FeedbackBundle\Form;

use Nines\FeedbackBundle\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * CommentType form.
 */
class CommentType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        $builder->add('fullname', null, [
            'label' => 'Your Name',
        ]);  // string
        $builder->add('email', null, [
            'label' => 'Your Email',
        ]);  // string
        $builder->add('followUp', ChoiceType::class, [
            'label' => 'Contact Me',
            'expanded' => true,
            'multiple' => false,
            'choices' => [
                'Yes' => true,
                'No' => false,
            ],
            'data' => 'false',
            'required' => true,
            'placeholder' => false,
            'attr' => [
                'help_block' => 'Would you like someone to contact you about this comment?',
            ],
        ]);
        $builder->add('content', TextareaType::class, [
            'label' => 'Suggestion',
            'attr' => [
                'class' => 'tinymce',
            ],
        ]);  // string
    }

    /**
     * Define options for the form.
     *
     * Set default, optional, and required options passed to the
     * buildForm() method via the $options parameter.
     */
    public function configureOptions(OptionsResolver $resolver) : void {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
