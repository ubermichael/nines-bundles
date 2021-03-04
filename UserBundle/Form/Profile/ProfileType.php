<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Form\Profile;

use Nines\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        $builder
            ->add('email')
            ->add('fullname')
            ->add('affiliation')
            ->add('password', PasswordType::class, [
                'label' => 'Confirm Password',
                'required' => true,
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) : void {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
