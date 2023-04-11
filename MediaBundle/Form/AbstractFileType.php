<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Form;

use Nines\MediaBundle\Entity\Audio;
use Nines\MediaBundle\Service\AbstractFileManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Audio form.
 */
class AbstractFileType extends AbstractType {
    /**
     * Add form fields to $builder.
     *
     * @param mixed $label
     */
    public function buildForm(FormBuilderInterface $builder, array $options, $label = 'File') : void {
        $maxSize = AbstractFileManager::getMaxUploadSize(false);
        $maxBytes = AbstractFileManager::getMaxUploadSize(true);
        $builder->add('file', FileType::class, [
            'label' => $label,
            'required' => true,
            'attr' => [
                'help_block' => "Select a file to upload which is less than {$maxSize} in size.",
                'data-maxsize' => $maxBytes,
            ],
        ]);

        $builder->add('public', ChoiceType::class, [
            'label' => 'Public',
            'expanded' => true,
            'multiple' => false,
            'required' => true,
            'choices' => [
                'No' => 0,
                'Yes' => 1,
            ],
            'attr' => [
                'help_block' => '',
            ],
        ]);
        $builder->add('description', TextareaType::class, [
            'label' => 'Description',
            'required' => false,
            'attr' => [
                'help_block' => '',
                'class' => 'tinymce',
            ],
        ]);
        $builder->add('license', TextareaType::class, [
            'label' => 'License',
            'required' => false,
            'attr' => [
                'help_block' => '',
                'class' => 'tinymce',
            ],
        ]);
    }
}
