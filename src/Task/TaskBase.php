<?php

declare(strict_types = 1);

namespace Pamald\Robo\Pamald\Task;

use Robo\Result;
use Robo\Task\BaseTask;
use Robo\TaskInfo;

abstract class TaskBase extends BaseTask
{

    /**
     * @var array<string, mixed>
     */
    protected array $assets = [];

    protected string $taskName = '';

    public function getTaskName(): string
    {
        return $this->taskName ?: TaskInfo::formatTaskName($this);
    }

    /**
     * @param null|array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    protected function getTaskContext($context = null): array
    {
        if (!$context) {
            $context = [];
        }

        if (empty($context['name'])) {
            $context['name'] = $this->getTaskName();
        }

        return parent::getTaskContext($context);
    }

    // region Option - assetNamePrefix.
    protected string $assetNamePrefix = '';

    public function getAssetNamePrefix(): string
    {
        return $this->assetNamePrefix;
    }

    public function setAssetNamePrefix(string $value): static
    {
        $this->assetNamePrefix = $value;

        return $this;
    }
    //endregion

    // region Option - assetNameSuffix
    protected string $assetNameSuffix = '';

    public function getAssetNameSuffix(): string
    {
        return $this->assetNameSuffix;
    }

    public function setAssetNameSuffix(string $assetNameSuffix): static
    {
        $this->assetNameSuffix = $assetNameSuffix;

        return $this;
    }
    // endregion

    /**
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): static
    {
        if (array_key_exists('assetNamePrefix', $options)) {
            $this->setAssetNamePrefix($options['assetNamePrefix']);
        }

        if (array_key_exists('assetNameSuffix', $options)) {
            $this->setAssetNameSuffix($options['assetNameSuffix']);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        return $this
            ->runHeader()
            ->runDoIt()
            ->runPrepareAssets()
            ->runReturn();
    }

    protected function runHeader(): static
    {
        $this->printTaskInfo('');

        return $this;
    }

    abstract protected function runDoIt(): static;

    protected function runPrepareAssets(): static
    {
        return $this;
    }

    protected function runReturn(): Result
    {
        return new Result(
            $this,
            $this->getTaskResultCode(),
            $this->getTaskResultMessage(),
            $this->getAssetsWithPrefixedNames()
        );
    }

    protected int $taskResultCode = 0;

    protected function getTaskResultCode(): int
    {
        return $this->taskResultCode;
    }

    protected function setTaskResultCode(int $code): static
    {
        $this->taskResultCode = $code;

        return $this;
    }

    protected string $taskResultMessage = '';

    protected function getTaskResultMessage(): string
    {
        return $this->taskResultMessage;
    }

    protected function setTaskResultMessage(string $message): static
    {
        $this->taskResultMessage = $message;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getAssetsWithPrefixedNames(): array
    {
        $prefix = $this->getAssetNamePrefix();
        $suffix = $this->getAssetNameSuffix();
        if ($prefix === '' && $suffix === '') {
            return $this->assets;
        }

        $assets = [];
        foreach ($this->assets as $key => $value) {
            $assets[$prefix . $key . $suffix] = $value;
        }

        return $assets;
    }
}
