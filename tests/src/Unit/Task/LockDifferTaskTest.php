<?php

declare(strict_types = 1);

namespace Pamald\Robo\Pamald\Tests\Unit\Task;

use Pamald\Robo\Pamald\PamaldTaskLoader;
use Pamald\Robo\Pamald\Task\LockDifferTask;
use Pamald\Robo\Pamald\Task\TaskBase;
use Pamald\Robo\Pamald\Tests\Helper\DummyPackage;
use Pamald\Robo\Pamald\Tests\Helper\DummyTaskBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(LockDifferTask::class)]
#[CoversClass(TaskBase::class)]
class LockDifferTaskTest extends TaskTestBase
{

    /**
     * @return array<string, mixed>
     */
    public static function casesRunSuccess(): array
    {
        return [
            'basic' => [
                'expected' => [
                    'exitCode' => 0,
                    'exitMessage' => '',
                    'assets' => [
                        'pamald.lockDiffEntries' => [
                            'a/a' => [
                                'name' => 'a/a',
                            ],
                        ],
                    ],
                ],
                'options' => [
                    'rightPackages' => [
                        'a/a' => new DummyPackage([
                            'name' => 'a/a',
                            'versionString' => '1.2.3',
                        ]),
                    ],
                ],
            ],
        ];
    }

    /**
     * @phpstan-param array<string, mixed> $expected
     * @phpstan-param robo-pamald-lock-differ-task-options $options
     */
    #[DataProvider('casesRunSuccess')]
    public function testRunSuccess(array $expected, array $options): void
    {
        $taskBuilder = new DummyTaskBuilder();
        $taskBuilder->setContainer($this->getNewContainer());
        $task = $taskBuilder->taskPamaldLockDiffer($options);
        $result = $task->run();

        static::assertSame($expected['exitCode'], $result->getExitCode());
        static::assertSame($expected['exitMessage'], $result->getMessage());
        static::assertSame(
            array_keys($expected['assets']['pamald.lockDiffEntries']),
            array_keys($result['pamald.lockDiffEntries']),
        );

        foreach ($expected['assets']['pamald.lockDiffEntries'] as $packageName => $expectedLockDiffEntry) {
            static::assertSame(
                $expectedLockDiffEntry['name'],
                $result['pamald.lockDiffEntries'][$packageName]->name,
            );
        }
    }
}
