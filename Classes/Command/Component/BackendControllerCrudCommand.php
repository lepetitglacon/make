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
use B13\Make\Exception\AbortCommandException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Command for creating a new backend controller component
 */
class BackendControllerCrudCommand extends SimpleComponentCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->setDescription('Create a backend controller');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->initializeServiceConfiguration();
        $this->initializeArrayConfiguration('Routes.php', 'Configuration/Backend/');
    }

    protected function createComponent(): ComponentInterface
    {
        $question = new ChoiceQuestion('Please choose the domain object', array_combine(array_keys($GLOBALS['TCA']), array_keys($GLOBALS['TCA'])));
        $bundles = array_keys($GLOBALS['TCA']);
        $question->setAutocompleterValues($bundles);

        $backendController = new BackendCrudController($this->psr4Prefix);
        return $backendController
            ->setDomainObject(
                'pages'
//                $this->io->askQuestion($question)
            )
//            ->setUseHeadless(
//                (bool)$this->io->ask(
//                    'Should use Headless ?',
//                    'false'
//                )
//            )
            ->setName(
                'pagesController'
//                (string)$this->io->ask(
//                    'Enter the name of the backend controller (e.g. "AwesomeController")',
//                    null,
//                    [$this, 'answerRequired']
//                )
            )
            ->setDirectory(
                'Classes/Controller'
//                (string)$this->io->ask(
//                    'Enter the directory, the backend controller should be placed in',
//                    $this->getProposalFromEnvironment('BACKEND_CONTROLLER_DIR', 'Backend/Controller')
//                )
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
            )
            ->setMethodName(
                (string)$this->io->ask('Enter the method, which should handle the request - LEAVE EMPTY FOR USING __invoke()')
            );
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
}
