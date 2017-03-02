<?php

namespace Nines\FeedbackBundle\Form;

use Nines\FeedbackBundle\Entity\Flag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FlagEntityType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {     
        $em = $options['entity_manager'];
        $flags = $options['flags'];
        
        $builder->add('flags', ChoiceType::class, array(
            'choices' => $em->getRepository(Flag::class)->findAll(),
            'choice_label' => function($value, $key, $index) {
                return $value->getLabel();
            },
            'choice_value' => function($value) {
                return $value->getId();
            },
            'label' => 'Flags',
            'required' => false,
            'expanded' => true,
            'multiple' => true,
            'data' => $flags,
        ));
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setRequired(array('entity_manager', 'flags'));
    }
}
