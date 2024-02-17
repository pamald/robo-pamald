<?php

declare(strict_types = 1);

namespace Pamald\Robo\Pamald\Task;

use Pamald\Pamald\LockDiffer;

class LockDifferTask extends TaskBase
{

    // region leftPackages
    /**
     * @var array<string, \Pamald\Pamald\PackageInterface>
     */
    protected array $leftPackages = [];

    /**
     * @return array<string, \Pamald\Pamald\PackageInterface>
     */
    public function getLeftPackages(): array
    {
        return $this->leftPackages;
    }

    /**
     * @param array<string, \Pamald\Pamald\PackageInterface> $leftPackages
     */
    public function setLeftPackages(array $leftPackages): static
    {
        $this->leftPackages = $leftPackages;

        return $this;
    }
    // endregion

    // region rightPackages
    /**
     * @var array<string, \Pamald\Pamald\PackageInterface>
     */
    protected array $rightPackages = [];

    /**
     * @return array<string, \Pamald\Pamald\PackageInterface>
     */
    public function getRightPackages(): array
    {
        return $this->rightPackages;
    }

    /**
     * @param array<string, \Pamald\Pamald\PackageInterface> $rightPackages
     */
    public function setRightPackages(array $rightPackages): static
    {
        $this->rightPackages = $rightPackages;

        return $this;
    }
    // endregion

    // region lockDiffer
    protected ?LockDiffer $lockDiffer = null;

    public function getLockDiffer(): ?LockDiffer
    {
        return $this->lockDiffer;
    }

    protected function getLockDifferMust(): LockDiffer
    {
        return $this->getLockDiffer() ?: new LockDiffer();
    }

    public function setLockDiffer(?LockDiffer $lockDiffer): static
    {
        $this->lockDiffer = $lockDiffer;

        return $this;
    }
    // endregion

    public function setOptions(array $options): static
    {
        parent::setOptions($options);

        if (array_key_exists('leftPackages', $options)) {
            $this->setLeftPackages($options['leftPackages']);
        }

        if (array_key_exists('rightPackages', $options)) {
            $this->setRightPackages($options['rightPackages']);
        }

        if (array_key_exists('lockDiffer', $options)) {
            $this->setLockDiffer($options['lockDiffer']);
        }

        return $this;
    }

    protected function runDoIt(): static
    {
        $this->assets['pamald.lockDiffEntries'] = $this
            ->getLockDifferMust()
            ->diff($this->getLeftPackages(), $this->getRightPackages());

        return $this;
    }
}
