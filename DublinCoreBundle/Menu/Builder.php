<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\DublinCoreBundle\Menu;

use Knp\Menu\ItemInterface;
use Nines\UtilBundle\Menu\AbstractBuilder;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class to build some menus for navigation.
 */
class Builder extends AbstractBuilder {
    use ContainerAwareTrait;

    /**
     * @param array<string,string> $options
     */
    public function dcNavMenu(array $options) : ItemInterface {
        $title = $options['title'] ?? 'Dublin Core';
        $menu = $this->dropdown($title);

        $menu->addChild('Elements', [
            'route' => 'nines_dc_element_index',
        ]);
        $menu->addChild('Values', [
            'route' => 'nines_dc_value_index',
        ]);

        return $menu->getParent();
    }
}
