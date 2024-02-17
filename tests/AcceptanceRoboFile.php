<?php

declare(strict_types = 1);

use Pamald\Pamald\Reporter\ConsoleTableReporter;
use Pamald\Robo\Pamald\PamaldTaskLoader;
use Pamald\Robo\Pamald\Tests\Helper\DummyPackage;
use Robo\Tasks;
use Robo\Contract\TaskInterface;
use Robo\State\Data as RoboState;
use Symfony\Component\Console\Helper\Table;

class AcceptanceRoboFile extends Tasks
{
    use PamaldTaskLoader;

    protected function output()
    {
        return $this->getContainer()->get('output');
    }

    /**
     * @command pamald:report
     */
    public function cmdPamaldReportExecute(): TaskInterface
    {
        $cb = $this->collectionBuilder();
        $cb
            ->addCode(function (RoboState $state): int {
                $state['leftPackages'] = [
                    'a/a' => new DummyPackage([
                        'name' => 'a/a',
                        'versionString' => '1.1.1',
                        'typeOfRelationship' => 'prod',
                        'isDirectDependency' => true,
                    ]),
                    'a/b' => new DummyPackage([
                        'name' => 'a/b',
                        'versionString' => '2.1.1',
                        'typeOfRelationship' => 'prod',
                        'isDirectDependency' => true,
                    ]),
                ];
                $state['rightPackages'] = [
                    'a/a' => new DummyPackage([
                        'name' => 'a/a',
                        'versionString' => '1.2.2',
                        'typeOfRelationship' => 'prod',
                        'isDirectDependency' => true,
                    ]),
                    'a/b' => new DummyPackage([
                        'name' => 'a/b',
                        'versionString' => '2.2.2',
                        'typeOfRelationship' => 'prod',
                        'isDirectDependency' => true,
                    ]),
                ];

                $reporter = new ConsoleTableReporter();
                $reporter->setTable(new Table($this->output()));
                $state['reporter'] = $reporter;

                return 0;
            })
            ->addTask(
                $this
                    ->taskPamaldLockDiffer()
                    ->deferTaskConfiguration('setLeftPackages', 'leftPackages')
                    ->deferTaskConfiguration('setRightPackages', 'rightPackages')
            )
            ->addTask(
                $this
                    ->taskPamaldReporter()
                    ->deferTaskConfiguration('setLockDiffEntries', 'pamald.lockDiffEntries')
                    ->deferTaskConfiguration('setReporter', 'reporter')
            );

        return $cb;
    }
}
