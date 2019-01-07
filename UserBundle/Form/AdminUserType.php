<?php

namespace Nines\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Special-purpose form type for administering users.
 */
class AdminUserType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $permissionLevels = $options['permission_levels'];
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
                'choices' => $permissionLevels,
                'choice_label' => function($value, $key, $index) {
                    return $value;
                },
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'attr' => array(
                    'help_block' => 'ROLE_ADMIN has access to everything. ROLE_CONTENT_ADMIN can add and update content.',
                ),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Nines\UserBundle\Entity\User',
            'permission_levels' => array(),
        ));
        $resolver->setAllowedTypes('permission_levels', 'array');
    }

    /**
     * {@inheritdoc}
     */
    public function getName() {
        return 'appbundle_user';
    }

}
