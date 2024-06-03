<?php

namespace B13\make\Classes\Component\Extension;

class FrontendPlugin
{
    protected $actions = [];

    public function getActions(): array
    {
        return $this->actions;
    }

    public function setActions(array $actions): void
    {
        $this->actions = $actions;
    }


}