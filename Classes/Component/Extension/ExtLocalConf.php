<?php

namespace B13\Make\Component\Extension;

use B13\Make\Component\AbstractFluidRenderedComponent;

class ExtLocalConf extends AbstractFluidRenderedComponent
{
    protected $name = 'ext_localconf.php';

    public function getFluidTemplatePath(): string
    {
        return 'EXT:make/Resources/Private/CodeTemplates/ext_localconf.html';
    }
}