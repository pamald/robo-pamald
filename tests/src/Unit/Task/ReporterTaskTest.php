<?php

declare(strict_types = 1);

namespace Pamald\Robo\Pamald\Tests\Unit\Task;

use Pamald\Pamald\LockDiffEntry;
use Pamald\Pamald\Reporter\ConsoleTableReporter;
use Pamald\Robo\Pamald\PamaldTaskLoader;
use Pamald\Robo\Pamald\Task\ReporterTask;
use Pamald\Robo\Pamald\Task\TaskBase;
use Pamald\Robo\Pamald\Tests\Helper\DummyPackage;
use Pamald\Robo\Pamald\Tests\Helper\DummyTaskBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ReporterTask::class)]
#[CoversClass(TaskBase::class)]
class ReporterTaskTest extends TaskTestBase
{

    /**
     * @return resource
     */
    protected static function createStream()
    {
        $filePath = 'php://memory';
        $resource = fopen($filePath, 'rw');
        if ($resource === false) {
            throw new \RuntimeException("file $filePath could not be opened");
        }

        return $resource;
    }

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
                    'rendered' => <<< 'TEXT'
                        +------+-----------+-----------+----------------+----------------+---------+---------+
                        | Name | L Version | R Version | L Relationship | R Relationship | L Depth | R Depth |
                        +------+-----------+-----------+----------------+----------------+---------+---------+
                        | a/a  | 1.0.0     | 1.2.3     | prod           | prod           | child   | direct  |
                        +------+-----------+-----------+----------------+----------------+---------+---------+

                        TEXT,
                ],
                'reporterOptions' => [],
                'options' => [
                    'lockDiffEntries' => [
                        'a/a' => new LockDiffEntry(
                            new DummyPackage([
                                'name' => 'a/a',
                                'versionString' => '1.0.0',
                                'typeOfRelationship' => 'prod',
                                'isDirectDependency' => false,
                            ]),
                            new DummyPackage([
                                'name' => 'a/a',
                                'versionString' => '1.2.3',
                                'typeOfRelationship' => 'prod',
                                'isDirectDependency' => true,
                            ]),
                        ),
                    ],
                ],
            ],
        ];
    }

    /**
     * @phpstan-param array<string, mixed> $expected
     * @phpstan-param array<string, mixed> $reporterOptions
     * @phpstan-param robo-pamald-reporter-task-options $options
     */
    #[DataProvider('casesRunSuccess')]
    public function testRunSuccess(array $expected, array $reporterOptions, array $options): void
    {
        $taskBuilder = new DummyTaskBuilder();
        $taskBuilder->setContainer($this->getNewContainer());

        $reporterOptions['stream'] = static::createStream();

        $options['reporter'] = (new ConsoleTableReporter())
                ->setOptions($reporterOptions);

        $task = $taskBuilder->taskPamaldReporter($options);
        $result = $task->run();

        rewind($reporterOptions['stream']);
        static::assertSame(
            $expected['rendered'],
            stream_get_contents($reporterOptions['stream']),
        );
        fclose($reporterOptions['stream']);

        static::assertSame($expected['exitCode'], $result->getExitCode());
        static::assertSame($expected['exitMessage'], $result->getMessage());
    }
}
