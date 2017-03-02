<?php

namespace Nines\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Special-purpose form type for administering users.
 */
class AdminUserType extends AbstractType
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
                ->add('enabled', CheckboxType::class, array(
                    'label' => 'Account Enabled',
                    'required' => false,
                ))
                ->add('roles', ChoiceType::class, array(
                    'label' => 'Roles',
                    'choices' => array(
                        'ROLE_ADMIN' => 'Admin',
                    ),
                    'multiple' => true,
                    'expanded' => true,
                    'required' => false,
                ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Nines\UserBundle\Entity\User',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'appbundle_user';
    }
}
