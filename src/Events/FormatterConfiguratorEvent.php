<?php

namespace Vigstudio\VgComment\Events;

use s9e\TextFormatter\Configurator;

class FormatterConfiguratorEvent
{
    public Configurator $configurator;

    public function __construct(Configurator $configurator)
    {
        $this->configurator = $configurator;
    }
}
