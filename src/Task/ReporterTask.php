<?php

declare(strict_types = 1);


namespace Pamald\Robo\Pamald\Task;

use Pamald\Pamald\ReporterInterface;

class ReporterTask extends TaskBase
{

    // region lockDiffEntries

    /**
     * @var array<string, \Pamald\Pamald\LockDiffEntry>
     */
    protected array $lockDiffEntries = [];

    /**
     * @return array<string, \Pamald\Pamald\LockDiffEntry>
     */
    public function getLockDiffEntries(): array
    {
        return $this->lockDiffEntries;
    }

    /**
     * @param array<string, \Pamald\Pamald\LockDiffEntry> $lockDiffEntries
     */
    public function setLockDiffEntries(array $lockDiffEntries): static
    {
        $this->lockDiffEntries = $lockDiffEntries;

        return $this;
    }
    // endregion

    // region reporter
    protected ReporterInterface $reporter;

    public function getReporter(): ReporterInterface
    {
        return $this->reporter;
    }

    public function setReporter(ReporterInterface $reporter): static
    {
        $this->reporter = $reporter;

        return $this;
    }
    // endregion

    public function setOptions(array $options): static
    {
        parent::setOptions($options);

        if (array_key_exists('lockDiffEntries', $options)) {
            $this->setLockDiffEntries($options['lockDiffEntries']);
        }

        if (array_key_exists('reporter', $options)) {
            $this->setReporter($options['reporter']);
        }

        return $this;
    }

    protected function runDoIt(): static
    {
        $this->getReporter()->generate($this->getLockDiffEntries());

        return $this;
    }
}
