<?php

declare(strict_types = 1);

namespace Pamald\Robo\Pamald\Task;

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Robo\Contract\BuilderAwareInterface;
use Robo\State\StateAwareInterface;
use Robo\State\StateAwareTrait;
use Robo\TaskAccessor;

abstract class CommitMsgCollectLockFilePathsTask extends TaskBase implements
    BuilderAwareInterface,
    ContainerAwareInterface,
    StateAwareInterface
{
    use TaskAccessor;
    use ContainerAwareTrait;
    use StateAwareTrait;

    // region lockFilePathsStateKey
    protected ?string $lockFilePathsStateKey = 'lockFilePaths';

    public function getLockFilePathsStateKey(): ?string
    {
        return $this->lockFilePathsStateKey;
    }

    public function setLockFilePathsStateKey(?string $key): static
    {
        $this->lockFilePathsStateKey = $key;

        return $this;
    }
    // endregion

    // region isLockFilePathsStateKeyFinal
    protected bool $isLockFilePathsStateKeyFinal = false;

    public function isLockFilePathsStateKeyFinal(): bool
    {
        return $this->isLockFilePathsStateKeyFinal;
    }

    public function setIsLockFilePathsStateKeyFinal(bool $isFinal): static
    {
        $this->isLockFilePathsStateKeyFinal = $isFinal;

        return $this;
    }
    // endregion

    // region sha1
    protected string $sha1 = 'HEAD';

    public function getSha1(): string
    {
        return $this->sha1;
    }

    public function setSha1(string $sha1): static
    {
        $this->sha1 = $sha1;

        return $this;
    }
    // endregion

    // region patterns
    /**
     * @var string[]
     */
    protected array $patterns = [
        'composer.lock',
        '**/composer.lock',
        'composer.*.lock',
        '**/composer.*.lock',
        'yarn.lock',
        '**/yarn.lock',
    ];

    /**
     * @return string[]
     */
    public function getPatterns(): array
    {
        return $this->patterns;
    }

    /**
     * @param string[] $patterns
     */
    public function setPatterns(array $patterns): static
    {
        $this->patterns = $patterns;

        return $this;
    }
    // endregion

    public function setOptions(array $options): static
    {
        parent::setOptions($options);

        if (array_key_exists('lockFilePathsStateKey', $options)) {
            $this->setLockFilePathsStateKey($options['lockFilePathsStateKey']);
        }

        if (array_key_exists('isLockFilePathsStateKeyFinal', $options)) {
            $this->setIsLockFilePathsStateKeyFinal($options['isLockFilePathsStateKeyFinal']);
        }

        if (array_key_exists('sha1', $options)) {
            $this->setSha1($options['sha1']);
        }

        if (array_key_exists('patterns', $options)) {
            $this->setPatterns($options['patterns']);
        }

        return $this;
    }

    protected function runDoIt(): static
    {
        $lockFilePaths = $this->getLockFilePaths();
        $stateKey = $this->getFinalLockFilePathsStateKey();
        if ($stateKey !== null) {
            $state = $this->getState();
            $lockFilePaths = array_merge(
                $state[$stateKey] ?? [],
                $lockFilePaths,
            );
            sort($lockFilePaths, \SORT_NATURAL);
            $state[$stateKey] = $lockFilePaths;

            // No need to emit assets.
            return $this;
        }

        sort($lockFilePaths, \SORT_NATURAL);
        $this->assets[$this->getAssetName()] = array_unique($lockFilePaths, \SORT_NATURAL);

        return $this;
    }

    /**
     * @return string[]
     */
    abstract protected function getLockFilePaths(): array;

    abstract protected function getAssetName(): string;

    protected function getFinalLockFilePathsStateKey(): ?string
    {
        $stateKey = $this->getLockFilePathsStateKey();
        if ($stateKey === null) {
            return null;
        }

        return $this->isLockFilePathsStateKeyFinal()
            ? $stateKey
            : $this->getAssetNamePrefix() . $stateKey . $this->getAssetNameSuffix();
    }
}
