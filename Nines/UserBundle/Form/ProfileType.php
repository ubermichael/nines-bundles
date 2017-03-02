<?php

namespace Nines\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Overrides the ProfileType from FOSUserBundle so admins can 
 * edit profiles.
 * 
 * see http://symfony.com/doc/master/bundles/FOSUserBundle/overriding_forms.html
 */
class ProfileType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->remove('username')
                ->add('email')
                ->add('fullname')
                ->add('institution')
                ->add('save', ButtonType::class, array('label' => 'Update'));
    }

    /**
     * Get the parent form.
     * 
     * @return string
     */
    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\ProfileFormType';
    }
	
    /**
     * {@inheritdoc}
     */
	public function getBlockPrefix()
    {
        return 'app_user_profile';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        $this->getBlockPrefix();
    }
}
