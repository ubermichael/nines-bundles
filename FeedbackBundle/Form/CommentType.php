<?php

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

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('fullname');  // string
        $builder->add('email');  // string
        $builder->add('followUp', ChoiceType::class, array(
            'expanded' => true,
            'multiple' => false,
            'choices' => array(
                'Yes' => true,
                'No' => false,
                ),
            'data' => 'false',
            'required' => true,
            'placeholder' => false,
            'attr' => array(
                'help_block' => 'Would you like someone to contact you about this comment?'
            ),
        ));
        $builder->add('content', TextareaType::class);  // string
    }

    /**
     * Define options for the form.
     *
     * Set default, optional, and required options passed to the
     * buildForm() method via the $options parameter.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => Comment::class
        ));
    }

}
