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
    /** @var string */
    protected $domainObjectPrefix = '';
    /** @var string */
    protected $domainObjectModel = '';
    /** @var string */
    protected $domainObjectRepository = '';

    public function getDomainObject(): string {
        return $this->domainObject;
    }

    public function setDomainObject(string $domainObject): BackendCrudController {
        $this->domainObject = $domainObject;
        return $this;
    }

    public function getDomainObjectPrefix(): string
    {
        return $this->domainObjectPrefix;
    }

    public function setDomainObjectPrefix(string $domainObjectPrefix): BackendCrudController
    {
        $this->domainObjectPrefix = $domainObjectPrefix;
        return $this;
    }

    public function getDomainObjectModel(): string
    {
        return $this->domainObjectModel;
    }

    public function setDomainObjectModel(string $domainObjectModel): BackendCrudController
    {
        $this->domainObjectModel = $domainObjectModel;
        return $this;
    }

    public function getDomainObjectRepository(): string
    {
        return $this->domainObjectRepository;
    }

    public function setDomainObjectRepository(string $domainObjectRepository): BackendCrudController
    {
        $this->domainObjectRepository = $domainObjectRepository;
        return $this;
    }

    public function getPsr4Prefix(): string
    {
        return $this->psr4Prefix;
    }

    public function getDomainObjectPrefixDefaults()
    {
        return $this->getPsr4Prefix() . 'Classes\Domain';
    }

    public function getDomainObjectModelDefaults()
    {
        return $this->domainObjectPrefix . '\Model\\' .
            ucfirst(GeneralUtility::underscoredToLowerCamelCase($this->getDomainObject()));
    }

    public function getDomainObjectRepositoryDefaults()
    {
        return $this->domainObjectPrefix . '\Respository\\' .
            ucfirst(GeneralUtility::underscoredToLowerCamelCase($this->getDomainObject())) .
            'Repository';
    }

    public function getDomainObjectControllerDefaults()
    {
        return ucfirst(GeneralUtility::underscoredToLowerCamelCase($this->getDomainObject())) .
            'Controller';
    }

    protected function getDomainObjectModelClassname()
    {
        return explode('\\',$this->getDomainObjectModel())[count(explode('\\',$this->getDomainObjectModel())) -1];
    }
    protected function getDomainObjectModelVariable()
    {
        return lcfirst(explode('\\',$this->getDomainObjectModel())[count(explode('\\',$this->getDomainObjectModel())) -1]);
    }
    protected function getDomainObjectRepositoryClassname()
    {
        return explode('\\',$this->getDomainObjectRepository())[count(explode('\\',$this->getDomainObjectModel())) -1];
    }
    protected function getDomainObjectRepositoryVariable()
    {
        return lcfirst(explode('\\',$this->getDomainObjectRepository())[count(explode('\\',$this->getDomainObjectModel())) -1]);
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
            'domainObject' => [
                'name' => ucfirst(GeneralUtility::underscoredToLowerCamelCase($this->getDomainObject())),
                'table' => $this->getDomainObject(),
                'prefix' => $this->getDomainObjectPrefix(),
                'modelClassname' => $this->getDomainObjectModelClassname(),
                'modelVariable' => $this->getDomainObjectModelVariable(),
                'repositoryClassname' => $this->getDomainObjectRepositoryClassname(),
                'repositoryVariable' => $this->getDomainObjectRepositoryVariable(),
            ],
            'domainObjectModel' => $this->getDomainObjectModel(),
            'domainObjectRepository' => $this->getDomainObjectRepository(),
            'actions' => [
                [
                    'name' => 'index'
                ],
                [
                    'name' => 'show'
                ],
                [
                    'name' => 'new'
                ],
                [
                    'name' => 'create'
                ],
                [
                    'name' => 'edit'
                ],
                [
                    'name' => 'update'
                ],
                [
                    'name' => 'delete'
                ],
            ]
        ]);
        return $standaloneView->render();
    }
}
