<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Form;

use Nines\MediaBundle\Entity\Audio;
use Nines\MediaBundle\Service\AudioManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Audio form.
 */
class AudioType extends AbstractType {
    /**
     * @var AudioManager
     */
    private $fileUploader;

    public function __construct(AudioManager $fileUploader) {
        $this->fileUploader = $fileUploader;
    }

    /**
     * Add form fields to $builder.
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        $builder->add('audioFile', FileType::class, [
            'label' => 'Audio File',
            'required' => true,
            'attr' => [
                'help_block' => "Select a file to upload which is less than {$this->fileUploader->getMaxUploadSize(false)} in size.",
                'data-maxsize' => $this->fileUploader->getMaxUploadSize(),
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
    }

    /**
     * Define options for the form.
     *
     * Set default, optional, and required options passed to the
     * buildForm() method via the $options parameter.
     */
    public function configureOptions(OptionsResolver $resolver) : void {
        $resolver->setDefaults([
            'data_class' => Audio::class,
        ]);
    }
}
