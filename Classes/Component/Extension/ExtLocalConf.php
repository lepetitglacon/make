<?php

namespace B13\make\Classes\Component\Extension;

use B13\Make\Component\AbstractComponent;
use B13\Make\Component\AbstractFluidRenderedComponent;
use B13\Make\Component\FluidRenderedComponentInterface;

class ExtLocalConf extends AbstractFluidRenderedComponent
{
    protected $plugins = [];

    public function getPlugins(): array
    {
        return $this->plugins;
    }

    public function setPlugins(array $plugins): ExtLocalConf
    {
        $this->plugins = $plugins;
        return $this;
    }

    public function getFluidTemplatePath(): string
    {
        return 'EXT:make/Resources/Private/CodeTemplates/ext_localconf.html';
    }
}