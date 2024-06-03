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

use B13\Make\Command\AbstractCommand;
use B13\Make\Component\ComponentInterface;
use B13\Make\Component\ServiceConfigurationComponentInterface;
use B13\Make\Exception\AbortCommandException;
use B13\Make\Exception\InvalidPackageException;
use B13\Make\IO\ArrayConfiguration;
use B13\Make\IO\ServiceConfiguration;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Abstract class for creating simple components with only one file and an array and/or service configuration
 */
abstract class MultipleComponentCommand extends SimpleComponentCommand
{
    /** @var ComponentInterface[] */
    protected array $components = [];

    abstract function createComponents();


    public function getComponents(): array
    {
        return $this->components;
    }
    public function setComponents(array $components): void
    {
        $this->components = $components;
    }
    public function addComponent(ComponentInterface $component): void
    {
        $this->components[] = $component;
    }
    public function removeComponent(ComponentInterface $component): void
    {
        if (in_array($component, $this->components)) {
            array_splice(
                $this->components,
                array_search($component, $this->components),
                1
            );
        }

    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->initializeServiceConfiguration();
        $this->initializeArrayConfiguration('Routes.php', 'Configuration/Backend/');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $components = $this->createComponents();

        foreach ($components as $component) {
            $absoluteComponentDirectory = $this->getAbsoluteComponentDirectory($component);

            if (!file_exists($absoluteComponentDirectory)) {
                try {
                    GeneralUtility::mkdir_deep($absoluteComponentDirectory);
                } catch (\Exception $e) {
                    $this->io->error('Creating of directory ' . $absoluteComponentDirectory . ' failed.');
                    return 1;
                }
            }

            // Use .php in case no file extension was given
            $fileInfo = pathinfo($component->getName());
            $filename = $fileInfo['filename'] . '.' . (($fileInfo['extension'] ?? false) ? $fileInfo['extension'] : 'php');

            $componentFile = rtrim($absoluteComponentDirectory, '/') . '/' . $filename;
            if (file_exists($componentFile)
                && !$this->io->confirm('The file ' . $componentFile . ' already exists. Do you want to override it?')
            ) {
                $this->io->note('Aborting component generation.');
                return 0;
            }

            if (!GeneralUtility::writeFile($componentFile, (string)$component)) {
                $this->io->error('Creating ' . $component->getName() . ' in ' . $componentFile . ' failed.');
                return 1;
            }

            try {
                if (!$this->publishComponentConfiguration($component)) {
                    return 1;
                }
            } catch (AbortCommandException $e) {
                $this->io->note($e->getMessage());
                return 0;
            }

            if ($this->showFlushCacheMessage) {
                $this->io->note('You might want to flush the cache now');
            }
        }

        return 0;
    }
}
