<?php

declare(strict_types = 1);

namespace Pamald\Robo\Pamald\Task;

use Robo\Contract\BuilderAwareInterface;
use Robo\TaskAccessor;
use Sweetchuck\Robo\Git\GitTaskLoader;

class CommitMsgCollectLockFilePathsRightTask extends CommitMsgCollectLockFilePathsTask implements BuilderAwareInterface
{

    use TaskAccessor;
    use GitTaskLoader;

    protected function getAssetName(): string
    {
        return 'commitMsg.lockFilePaths.right';
    }

    protected function getLockFilePaths(): array
    {
        // @phpstan-ignore-next-line
        $result = $this
            ->taskGitListStagedFiles()
            ->setPaths($this->getPatterns())
            ->setFilePathStyle('relativeToTopLevel')
            ->run();

        // @todo Error handling.
        return $result['fileNames'] ?? [];
    }
}
