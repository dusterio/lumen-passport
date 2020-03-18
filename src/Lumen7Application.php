<?php

namespace Dusterio\LumenPassport;

use Laravel\Lumen\Application;

class Lumen7Application extends Application {
    /**
     * @return bool
     */
    public function configurationIsCached()
    {
        return false;
    }
}