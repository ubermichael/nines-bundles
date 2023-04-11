<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\FeedbackBundle\Menu;

use Knp\Menu\ItemInterface;
use Nines\UtilBundle\Menu\AbstractBuilder;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class to build some menus for navigation.
 */
class Builder extends AbstractBuilder {
    use ContainerAwareTrait;

    /**
     * @param array<string,mixed> $options
     */
    public function feedbackMenu(array $options) : ItemInterface {
        $title = $options['title'] ?? 'Feedback';
        $feedback = $this->dropdown($title);

        $feedback->addChild('Comments', [
            'route' => 'nines_feedback_comment_index',
        ]);
        $feedback->addChild('Comment Notes', [
            'route' => 'nines_feedback_comment_note_index',
        ]);
        $this->addDivider($feedback);
        $feedback->addChild('Comment States', [
            'route' => 'nines_feedback_comment_status_index',
        ]);

        return $feedback->getParent();
    }
}
