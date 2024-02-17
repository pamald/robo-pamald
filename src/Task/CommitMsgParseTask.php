<?php

declare(strict_types = 1);

namespace Sweetchuck\GitHooksHelpers\Robo\Task;

namespace Pamald\Robo\Pamald\Task;

use Sweetchuck\Git\CommitMsg\CommitMsgHandler;

class CommitMsgParseTask extends TaskBase
{

    // region filePath
    protected string $filePath = '';

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
    }
    // endregion

    // region handler
    protected ?CommitMsgHandler $handler = null;

    public function getHandler(): ?CommitMsgHandler
    {
        return $this->handler;
    }

    public function setHandler(?CommitMsgHandler $handler): static
    {
        $this->handler = $handler;

        return $this;
    }
    // endregion

    // region modifiers
    /**
     * @phpstan-var array<string, sweetcuck-git-commit-msg-part-modifier>
     */
    protected array $modifiers = [];

    /**
     * @phpstan-return array<string, sweetcuck-git-commit-msg-part-modifier>
     */
    public function getModifiers(): array
    {
        return $this->modifiers;
    }

    /**
     * @phpstan-param array<string, sweetcuck-git-commit-msg-part-modifier> $modifiers
     */
    public function setModifiers(array $modifiers): static
    {
        $this->modifiers = $modifiers;

        return $this;
    }
    // endregion

    public function setOptions(array $options): static
    {
        parent::setOptions($options);
        if (array_key_exists('filePaths', $options)) {
            $this->setFilePath($options['filePath']);
        }

        if (array_key_exists('handler', $options)) {
            $this->setHandler($options['handler']);
        }

        if (array_key_exists('modifiers', $options)) {
            $this->setModifiers($options['modifiers']);
        }

        return $this;
    }

    protected function getFinalHandler(): CommitMsgHandler
    {
        return $this->getHandler() ?: new CommitMsgHandler();
    }

    protected function runDoIt(): static
    {
        $handler = $this->getFinalHandler();
        $this->assets['commitMsg.parts'] = $handler->parse(
            file_get_contents($this->getFilePath()) ?: '',
            $this->getModifiers(),
        );

        return $this;
    }
}
