<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Form;

use Nines\MediaBundle\Entity\Pdf;
use Nines\MediaBundle\Service\PdfManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Pdf form.
 */
class PdfType extends AbstractType {
    /**
     * @var PdfManager
     */
    private $pdfManager;

    public function __construct(PdfManager $pdfManager) {
        $this->pdfManager = $pdfManager;
    }

    /**
     * Add form fields to $builder.
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        $builder->add('file', FileType::class, [
            'label' => 'Pdf',
            'required' => true,
            'attr' => [
                'help_block' => "Select a file to upload which is less than {$this->pdfManager->getMaxUploadSize(false)} in size.",
                'data-maxsize' => $this->pdfManager->getMaxUploadSize(),
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

    /**
     * Define options for the form.
     *
     * Set default, optional, and required options passed to the
     * buildForm() method via the $options parameter.
     */
    public function configureOptions(OptionsResolver $resolver) : void {
        $resolver->setDefaults([
            'data_class' => Pdf::class,
        ]);
    }
}
