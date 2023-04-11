<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserPasswordType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        $builder->add('new_password', RepeatedType::class, [
            'invalid_message' => 'The password fields must match.',
            'options' => [
                'attr' => [
                    'class' => 'password-field',
                ],
            ],
            'required' => true,
            'first_options' => [
                'label' => 'New Password',
            ],
            'second_options' => [
                'label' => 'Repeat Password',
            ],
            'type' => PasswordType::class,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) : void {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
