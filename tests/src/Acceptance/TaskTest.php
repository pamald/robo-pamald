<?php

declare(strict_types = 1);

namespace Pamald\Robo\Pamald\Tests\Acceptance;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class TaskTest extends TestCase
{

    public function testRoboTaskPamaldReport(): void
    {
        $actual = $this->runRoboCommand(['pamald:report']);
        $expected = [
            'exitCode' => 0,
            'out' => <<< 'Text'
                +------+-----------+-----------+----------------+----------------+---------+---------+
                | Name | L Version | R Version | L Relationship | R Relationship | L Depth | R Depth |
                +------+-----------+-----------+----------------+----------------+---------+---------+
                | a/a  | 1.1.1     | 1.2.2     | prod           | prod           | direct  | direct  |
                | a/b  | 2.1.1     | 2.2.2     | prod           | prod           | direct  | direct  |
                +------+-----------+-----------+----------------+----------------+---------+---------+

                Text,
            'err' => implode(
                "\n",
                [
                    ' [Pamald\Robo\Pamald\Task\LockDifferTask] ',
                    ' [Pamald\Robo\Pamald\Task\ReporterTask] ',
                    '',
                ],
            ),
        ];

        static::assertSame($expected['exitCode'], $actual['exitCode']);
        static::assertSame($expected['out'], $actual['out']);
        static::assertSame($expected['err'], $actual['err']);
    }

    /**
     * @param string[] $command
     *
     * @phpstan-return cli-execute-result
     */
    protected function runRoboCommand(array $command = []): array
    {
        $binDir = './vendor/bin';
        $roboFile = './tests/AcceptanceRoboFile.php';
        $finalCommand = array_merge(
            [
                "$binDir/robo",
                '--no-ansi',
                "--load-from=$roboFile",
            ],
            $command
        );
        $process = new Process($finalCommand);
        $result = [
            'exitCode' => 0,
            Process::OUT => '',
            Process::ERR => '',

        ];
        $callback = function (string $type, string $data) use (&$result): void {
            $result[$type] .= $data;
        };
        $result['exitCode'] = $process->run($callback);

        // @phpstan-ignore-next-line
        return $result;
    }
}
