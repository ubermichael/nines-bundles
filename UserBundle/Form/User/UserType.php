<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Form\User;

use Nines\UserBundle\Entity\User;
use Nines\UserBundle\Services\UserManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType {
    private $manager;

    public function __construct(UserManager $manager) {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        $builder
            ->add('active')
            ->add('email')
            ->add('fullname')
            ->add('affiliation')
            ->add('roles', ChoiceType::class, [
                'choices' => $this->manager->getRoles(),
                'choice_label' => function ($value, $key, $index) {
                    return $value;
                },
                'expanded' => true,
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) : void {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}