<?php

declare(strict_types=1);

/*
 * (c) 2023 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Form;

use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class TreeChoiceType extends AbstractType {
    /**
     * @param array|Collection $choices
     */
    protected function buildTreeChoices($choices, int $level = 0) : array {
        $result = [];

        foreach ($choices as $choice) {
            $result[] = new ChoiceView($choice, (string) $choice->getId(), str_repeat(' - ', $level) . ' ' . $choice->__toString(), []);
            if ( ! $choice->getChildren()->isEmpty()) {
                $result = array_merge($result, $this->buildTreeChoices($choice->getChildren(), $level + 1));
            }
        }

        return $result;
    }

    public function buildView(FormView $view, FormInterface $form, array $options) : void {
        $choices = [];
        foreach ($view->vars['choices'] as $choice) {
            if (null === $choice->data->getParent()) {
                $choices[$choice->value] = $choice->data;
            }
        }
        $choices = $this->buildTreeChoices($choices);
        $view->vars['choices'] = $choices;
    }

    public function getParent() : string {
        return EntityType::class;
    }
}
