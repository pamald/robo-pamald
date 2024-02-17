<?php

declare(strict_types = 1);

namespace Pamald\Robo\Pamald\Task;

class CommitMsgInitInputsTask extends TaskBase
{
    // region filePath
    protected string $filePath = '';

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * File path which contains the prepared commit message.
     *
     * Usually it is "./.git/COMMIT_EDITMSG".
     */
    public function setFilePath(string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
    }
    // endregion

    // region messageSource
    protected string $messageSource = '';

    public function getMessageSource(): string
    {
        return $this->messageSource;
    }

    public function setMessageSource(string $messageSource): static
    {
        $this->messageSource = $messageSource;

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

    public function setOptions(array $options): static
    {
        parent::setOptions($options);

        if (array_key_exists('filePath', $options)) {
            $this->setFilePath($options['filePath']);
        }

        if (array_key_exists('messageSource', $options)) {
            $this->setMessageSource($options['messageSource']);
        }

        if (array_key_exists('sha1', $options)) {
            $this->setSha1($options['sha1']);
        }

        return $this;
    }

    protected function runDoIt(): static
    {
        $this->assets['commitMsg.filePath'] = $this->getFilePath();
        $this->assets['commitMsg.messageSource'] = $this->getMessageSource();
        $this->assets['commitMsg.sha1'] = $this->getSha1();

        return $this;
    }
}
