<?php

declare(strict_types = 1);

namespace Pamald\Robo\Pamald;

use Pamald\Robo\Pamald\Task\LockDifferTask;
use Pamald\Robo\Pamald\Task\ReporterTask;

trait PamaldTaskLoader
{
    /**
     * @phpstan-param robo-pamald-lock-differ-task-options $options
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
     * @phpstan-param robo-pamald-reporter-task-options $options
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
