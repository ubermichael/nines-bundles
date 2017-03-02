<?php

namespace Nines\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class NinesUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
