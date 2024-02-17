<?php

declare(strict_types = 1);

namespace Pamald\Robo\Pamald\Task;

use Symfony\Component\Process\Process;

class CommitMsgCollectLockFilePathsLeftTask extends CommitMsgCollectLockFilePathsTask
{

    // region processClass
    /**
     * @phpstan-var class-string<\Symfony\Component\Process\Process>
     */
    protected string $processClass = Process::class;

    /**
     * @phpstan-return class-string<\Symfony\Component\Process\Process>
     */
    public function getProcessClass(): string
    {
        return $this->processClass;
    }

    /**
     * @param class-string<\Symfony\Component\Process\Process> $processClass
     */
    public function setProcessClass(string $processClass): static
    {
        $this->processClass = $processClass;

        return $this;
    }
    // endregion

    public function setOptions(array $options): static
    {
        parent::setOptions($options);

        if (array_key_exists('processClass', $options)) {
            $this->setProcessClass($options['processClass']);
        }

        return $this;
    }

    protected function getAssetName(): string
    {
        return 'commitMsg.lockFilePaths.left';
    }

    /**
     * {@inheritdoc}
     */
    protected function getLockFilePaths(): array
    {
        // @todo Support for --git-dir.
        $ref = $this->getSha1();
        $patterns = $this->getPatterns();

        $command = [
            'git',
            'diff-tree',
            '--diff-filter=d',
            '-z',
            '--no-commit-id',
            '--name-only',
            $ref,
            '-r',
            '--',
            ...$patterns,
        ];

        $processClass = $this->getProcessClass();
        $process = new $processClass($command);
        // @todo Error handling.
        $process->run();

        return array_filter(
            explode("\0", $process->getOutput()),
        );
    }
}
