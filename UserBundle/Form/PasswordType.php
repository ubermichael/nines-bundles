<?php

namespace Nines\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Override the PasswordType form so admins can change passwords.
 */
class PasswordType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('save', ButtonType::class, array('label' => 'Update'));
    }

    /**
     * Get the parent form name.
     *
     * @return string
     */
    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\ChangePasswordFormType';
    }

    /**
     * {@inheritdoc}
     */
	public function getBlockPrefix()
    {
        return 'app_user_password';
    }
	
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        $this->getBlockPrefix();
    }
	
}
