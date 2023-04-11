<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Form\Admin;

use Nines\UserBundle\Entity\User;
use Nines\UserBundle\Services\UserManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType {
    private ?UserManager $manager = null;

    public function __construct(UserManager $manager) {
        $this->manager = $manager;
    }

    /**
     * @param array<string,mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        $builder
            ->add('active', ChoiceType::class, [
                'label' => 'Active',
                'expanded' => true,
                'multiple' => false,
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
                'required' => true,
                'placeholder' => false,
            ])
            ->add('email', EmailType::class)
            ->add('fullname', TextType::class, [
                'label' => 'Full Name',
            ])
            ->add('affiliation')
            ->add('roles', ChoiceType::class, [
                'choices' => $this->manager->getRoles(),
                'choice_label' => fn($value, $key, $index) => $value,
                'expanded' => true,
                'multiple' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) : void {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
