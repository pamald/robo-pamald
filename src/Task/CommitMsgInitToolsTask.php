<?php

declare(strict_types = 1);

namespace Pamald\Robo\Pamald\Task;

use Sweetchuck\Git\CommitMsg\CommitMsgHandler;

class CommitMsgInitToolsTask extends TaskBase
{

    protected function runValidate(): static
    {
        return $this;
    }

    protected function runDoIt(): static
    {
        $this->assets['commitMsg.handler'] = new CommitMsgHandler();
        $this->assets['commitMsg.handler.partsModifiers'] = [];

        return $this;
    }
}
