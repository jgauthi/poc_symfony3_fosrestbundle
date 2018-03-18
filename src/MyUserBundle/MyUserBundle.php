<?php

namespace MyUserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MyUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
