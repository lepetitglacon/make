<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 CMS-based extension "b13/make" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace B13\Make\Component;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Backend controller component
 */
class BackendCrudController extends BackendController
{
    /** @var string */
    protected $domainObject = '';

    /** @var bool */
    protected $useHeadless = false;

    /** @var string */
    protected $routeIdentifier = '';

    /** @var string */
    protected $routePath = '';

    /** @var string */
    protected $methodName = '';

    public function getDomainObject(): string
    {
        return $this->domainObject;
    }

    public function setDomainObject(string $domainObject): BackendController
    {
        $this->domainObject = $domainObject;
        return $this;
    }

    public function getUseHeadless(): bool
    {
        return $this->useHeadless;

    }

    public function setUseHeadless(bool $useHeadless): BackendController
    {
        $this->useHeadless = $useHeadless;
        return $this;
    }



    public function getRouteIdentifier(): string
    {
        return $this->routeIdentifier;
    }

    public function getRouteIdentifierProposal(string $prefix): string
    {
        return 'tx_' . trim($prefix, '_') . '_' . mb_strtolower(
            trim(str_replace('Controller', '', preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $this->name)), '_'),
            'utf-8'
        );
    }

    public function setRouteIdentifier(string $routeIdentifier): BackendController
    {
        $this->routeIdentifier = trim(str_replace('-', '_', $routeIdentifier), '_');
        return $this;
    }

    public function getRoutePathProposal(): string
    {
        return mb_strtolower(
            '/' . trim(str_replace('_', '/', str_replace('tx_', '', $this->routeIdentifier)), '/)'),
            'utf-8'
        );
    }

    public function setRoutePath(string $routePath): BackendController
    {
        $this->routePath = '/' . trim($routePath, '/');
        return $this;
    }

    public function setMethodName(string $methodName): BackendController
    {
        $this->methodName = $methodName;
        return $this;
    }

    public function getArrayConfiguration(): array
    {
        return  [
            'path' => $this->routePath,
            'target' => $this->getClassName() . ($this->methodName !== '' ? '::' . $this->methodName : ''),
        ];
    }

    public function __toString(): string
    {
        /** @var StandaloneView $standaloneView */
        $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
        $templatePathAndFile = 'EXT:make/Resources/Private/CodeTemplates/BackendCrudController.html';
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($templatePathAndFile));
        $standaloneView->assignMultiple([
            'namespace' => $this->getNamespace(),
            'name' => $this->name,
            'actions' => [
                'index', 'new', 'create', 'edit', 'update', 'delete'
            ]
        ]);
        return $standaloneView->render();
    }

    public function getServiceConfiguration(): array
    {
        return [
            $this->getClassName() => [
                'tags' => [
                    'backend.controller',
                ],
            ],
        ];
    }
}
