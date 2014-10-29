<?php

namespace Sqwack;

use Sqwack\Command as Commands;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    public function __construct($name, $version)
    {
        parent::__construct($name, $version);

        $this->add(new Commands\CronCommand());
        $this->add(new Commands\SnapCommand());
    }
}
