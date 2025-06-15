<?php

declare(strict_types = 1);

namespace Pamald\Robo\Pamald;

use Pamald\Robo\Pamald\Task\LockDifferTask;
use Pamald\Robo\Pamald\Task\ReporterTask;

/**
 * @phpstan-import-type RoboPamaldLockDifferTaskOptions from \Pamald\Robo\Pamald\Phpstan
 * @phpstan-import-type RoboPamaldReporterTaskOptions from \Pamald\Robo\Pamald\Phpstan
 */
trait PamaldTaskLoader
{
    /**
     * @phpstan-param RoboPamaldLockDifferTaskOptions $options
     *
     * @return \Pamald\Robo\Pamald\Task\LockDifferTask|\Robo\Collection\CollectionBuilder
     */
    protected function taskPamaldLockDiffer(array $options = [])
    {
        /** @var \Pamald\Robo\Pamald\Task\LockDifferTask $task */
        $task = $this->task(LockDifferTask::class);
        $task->setOptions($options);

        return $task;
    }

    /**
     * @phpstan-param RoboPamaldReporterTaskOptions $options
     *
     * @return \Pamald\Robo\Pamald\Task\ReporterTask|\Robo\Collection\CollectionBuilder
     */
    protected function taskPamaldReporter(array $options = [])
    {
        /** @var \Pamald\Robo\Pamald\Task\ReporterTask $task */
        $task = $this->task(ReporterTask::class);
        $task->setOptions($options);

        return $task;
    }
}
