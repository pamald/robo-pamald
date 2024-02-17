<?php

declare(strict_types = 1);

namespace Pamald\Robo\Pamald\Tests\Unit\Task;

use League\Container\Container as LeagueContainer;
use League\Container\DefinitionContainerInterface;
use Pamald\Robo\Pamald\Tests\Helper\DummyOutput;
use Pamald\Robo\Pamald\Tests\Helper\DummyTaskBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Robo\Collection\CollectionBuilder;
use Robo\Config\Config as RoboConfig;
use Robo\Robo;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\ErrorHandler\BufferingLogger;

abstract class TaskTestBase extends TestCase
{

    protected ContainerInterface $container;

    protected RoboConfig $config;

    protected CollectionBuilder $builder;

    protected DummyTaskBuilder $taskBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new LeagueContainer();
        $application = new SymfonyApplication('pamald', '1.0.0');
        $this->config = (new RoboConfig());
        $input = null;
        $output = new DummyOutput(OutputInterface::VERBOSITY_DEBUG);

        $this->container->add('container', $this->container);

        Robo::configureContainer($this->container, $application, $this->config, $input, $output);
        $this->container->addShared('logger', BufferingLogger::class);

        /** @var \Robo\Tasks $null */
        $null = null;
        $this->builder = CollectionBuilder::create($this->container, $null);
        $this->taskBuilder = new DummyTaskBuilder();
        $this->taskBuilder->setContainer($this->container);
        $this->taskBuilder->setBuilder($this->builder);
    }

    protected function getNewContainer(): DefinitionContainerInterface
    {
        $output = new DummyOutput(
            OutputInterface::VERBOSITY_DEBUG,
            false,
        );

        /** @var \League\Container\DefinitionContainerInterface $container */
        $container = Robo::createContainer();
        $container->add('output', $output);

        return $container;
    }
}
