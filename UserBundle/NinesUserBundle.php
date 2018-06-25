<?php

namespace Nines\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Create and define the bundle.
 */
class NinesUserBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
