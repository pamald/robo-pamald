<?php

declare(strict_types = 1);

namespace Pamald\Robo\Pamald;

use Pamald\Robo\Pamald\Task\CommitMsgCollectLockFilePathsLeftTask;
use Pamald\Robo\Pamald\Task\CommitMsgCollectLockFilePathsRightTask;
use Pamald\Robo\Pamald\Task\CommitMsgInitInputsTask;
use Pamald\Robo\Pamald\Task\CommitMsgInitToolsTask;
use Pamald\Robo\Pamald\Task\CommitMsgParseTask;

trait PrepareCommitMsgTaskLoader
{

    /**
     * @return \Pamald\Robo\Pamald\Task\CommitMsgInitInputsTask|\Robo\Collection\CollectionBuilder
     */
    protected function taskPamaldPrepareCommitMsgInitInputs(array $options = [])
    {
        /** @var \Pamald\Robo\Pamald\Task\CommitMsgInitInputsTask $task */
        $task = $this->task(CommitMsgInitInputsTask::class);
        $task->setOptions($options);

        return $task;
    }

    /**
     * @return \Pamald\Robo\Pamald\Task\CommitMsgInitToolsTask|\Robo\Collection\CollectionBuilder
     */
    protected function taskPamaldPrepareCommitMsgInitTools(array $options = [])
    {
        /** @var \Pamald\Robo\Pamald\Task\CommitMsgInitToolsTask $task */
        $task = $this->task(CommitMsgInitToolsTask::class);
        $task->setOptions($options);

        return $task;
    }

    /**
     * @return \Pamald\Robo\Pamald\Task\CommitMsgParseTask|\Robo\Collection\CollectionBuilder
     */
    protected function taskPamaldPrepareCommitMsgParse(array $options = [])
    {
        /** @var \Pamald\Robo\Pamald\Task\CommitMsgParseTask $task */
        $task = $this->task(CommitMsgParseTask::class);
        $task->setOptions($options);

        return $task;
    }

    /**
     * @return \Pamald\Robo\Pamald\Task\CommitMsgCollectLockFilePathsLeftTask|\Robo\Collection\CollectionBuilder
     */
    protected function taskPamaldPrepareCommitMsgCollectLockFilePathsLeft(array $options = [])
    {
        /** @var \Pamald\Robo\Pamald\Task\CommitMsgCollectLockFilePathsLeftTask $task */
        $task = $this->task(CommitMsgCollectLockFilePathsLeftTask::class);
        $task->setContainer($this->getContainer());
        $task->setOptions($options);

        return $task;
    }

    /**
     * @return \Pamald\Robo\Pamald\Task\CommitMsgCollectLockFilePathsRightTask|\Robo\Collection\CollectionBuilder
     */
    protected function taskGitHooksHelpersPrepareCommitMsgCollectLockFilePathsRight(array $options = [])
    {
        /** @var \Pamald\Robo\Pamald\Task\CommitMsgCollectLockFilePathsRightTask $task */
        $task = $this->task(CommitMsgCollectLockFilePathsRightTask::class);
        $task->setContainer($this->getContainer());
        $task->setOptions($options);

        return $task;
    }
}
