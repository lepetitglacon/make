<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 CMS-based extension "b13/make" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace B13\Make\Command\Component;

use B13\Make\Component\BackendController;
use B13\Make\Component\BackendCrudController;
use B13\Make\Component\ComponentInterface;
use B13\Make\Component\Extension\ExtLocalConf;
use B13\Make\Exception\AbortCommandException;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Command for creating a new backend controller component
 */
class BackendControllerCrudCommand extends MultipleComponentCommand
{
    function createComponents()
    {
        $this->io->writeln('We will create : ' . implode(',', ['zz', 'yy']) . ' components');
        $this->io->writeln('Backend controller ->');

        $backendController = new BackendCrudController($this->psr4Prefix);
        $backendController
            ->setDomainObject(
                $this->io->askQuestion($this->getTcaObjectsQuestion())
            )
            ->setDomainObjectPrefix(
                (string)$this->io->ask(
                    'What is the domainObject prefix',
                    $backendController->getDomainObjectPrefixDefaults(),
                    [$this, 'answerRequired']
                )
            )
            ->setDomainObjectModel(
                (string)$this->io->ask(
                    'What is the domainObject Model',
                    $backendController->getDomainObjectModelDefaults(),
                    [$this, 'answerRequired']
                )
            )
            ->setDomainObjectRepository(
                (string)$this->io->ask(
                    'What is the domainObject Repository',
                    $backendController->getDomainObjectRepositoryDefaults(),
                    [$this, 'answerRequired']
                )
            )
            ->setName(
                (string)$this->io->ask(
                    'Enter the name of the backend controller (e.g. "AwesomeController")',
                    $backendController->getDomainObjectControllerDefaults(),
                    [$this, 'answerRequired']
                )
            )
            ->setDirectory(
                (string)$this->io->ask(
                    'Enter the directory, the backend controller should be placed in',
                    $this->getProposalFromEnvironment('BACKEND_CONTROLLER_DIR', 'Classes/Controller')
                )
            )
            ->setRouteIdentifier(
                (string)$this->io->ask(
                    'Enter the route identifier for the backend controller',
                    $backendController->getRouteIdentifierProposal($this->getProposalFromEnvironment('BACKEND_CONTROLLER_PREFIX', $this->extensionKey))
                )
            )
            ->setRoutePath(
                (string)$this->io->ask(
                    'Enter the route path of the backend controller?',
                    $backendController->getRoutePathProposal()
                )
            );
        $this->components[] = $backendController;

        if (strtolower($this->io->ask(
            'Create frontend plugin ? Y/n',
            'Y',
            [$this, 'answerRequired']
        )) === 'y') {
            $extLocalConf = new ExtLocalConf($this->psr4Prefix);
            $extLocalConf
                ->addVariable('plugins', [
                    $this->io->ask(
                        'Groupe all action in one frontend plugin ?',
                        $backendController->getDomainObjectModelClassname() . 'Plugin',
                        [$this, 'answerRequired']
                    )
                ])
                ->addVariable('extensionName', ucfirst(GeneralUtility::underscoredToLowerCamelCase($this->extensionKey)))
                ->addVariable('extensionKey', $this->extensionKey)
                ->addVariable('actions', $backendController->actions)
                ->addVariable('controllerUse', $backendController->getClassName())
                ->addVariable('controllerName', $backendController->getName())
                ->addVariable('controllerName', $backendController->getName())
            ;
            $this->components[] = $extLocalConf;
        }


        return $this->components;
    }

    protected function createComponent(): ComponentInterface
    {
        return new BackendCrudController($this->psr4Prefix);
    }

    /**
     * @param BackendController $component
     * @throws AbortCommandException
     */
    protected function publishComponentConfiguration(ComponentInterface $component): bool
    {
        if (!$this->writeServiceConfiguration($component)) {
            $this->io->error('Updating the service configuration failed.');
            return false;
        }

        $routeConfiguration = $this->arrayConfiguration->getConfiguration();
        if (isset($routeConfiguration[$component->getRouteIdentifier()])
            && !$this->io->confirm('The route identifier ' . $component->getRouteIdentifier() . ' already exists. Do you want to override it?', true)
        ) {
            throw new AbortCommandException('Aborting backend controller generation.', 1639664754);
        }

        $routeConfiguration[$component->getRouteIdentifier()] = $component->getArrayConfiguration();
        $this->arrayConfiguration->setConfiguration($routeConfiguration);
        if (!$this->writeArrayConfiguration()) {
            $this->io->error('Updating the routing configuration failed.');
            return false;
        }

        $this->io->success('Successfully created the backend controller ' . $component->getName() . ' (' . $component->getRouteIdentifier() . ').');
        return true;
    }

    protected function getTcaObjectsQuestion() : Question
    {
        $question = new ChoiceQuestion(
            'Please choose the domain object',
            array_combine(
                array_keys($GLOBALS['TCA']),
                array_keys($GLOBALS['TCA'])
            ),
            'pages'
        );
        $TcaObjects = array_keys($GLOBALS['TCA']);
        $question->setAutocompleterValues($TcaObjects);
        return $question;
    }
}
