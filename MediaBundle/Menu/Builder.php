<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Menu;

use Knp\Menu\ItemInterface;
use Nines\UtilBundle\Menu\AbstractBuilder;

/**
 * Class to build some menus for navigation.
 */
class Builder extends AbstractBuilder {
    /**
     * @param array<string,mixed> $options
     */
    public function navMenu(array $options) : ItemInterface {
        $title = $options['title'] ?? 'Media';
        $menu = $this->dropdown($title);

        $menu->addChild('Audio Files', [
            'route' => 'nines_media_audio_index',
        ]);
        $menu->addChild('Images', [
            'route' => 'nines_media_image_index',
        ]);
        $menu->addChild('Pdfs', [
            'route' => 'nines_media_pdf_index',
        ]);
        $this->addDivider($menu);
        $menu->addChild('Links', [
            'route' => 'nines_media_link_index',
        ]);

        return $menu->getParent();
    }
}
