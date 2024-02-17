<?php

declare(strict_types = 1);

namespace Pamald\Robo\Pamald\Task;

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Pamald\Pamald\LockDiffer;
use Pamald\Pamald\PackageCollectorInterface;
use Pamald\Pamald\Reporter\MarkdownTableReporter;
use Pamald\Robo\Pamald\PamaldTaskLoader;
use Robo\Contract\BuilderAwareInterface;
use Robo\State\StateAwareInterface;
use Robo\State\StateAwareTrait;
use Robo\TaskAccessor;
use Sweetchuck\Robo\Git\GitTaskLoader;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Process\Process;

abstract class ModifyCommitMsgPartsTaskBase extends TaskBase implements
    BuilderAwareInterface,
    ContainerAwareInterface,
    StateAwareInterface
{
    use ContainerAwareTrait;
    use StateAwareTrait;
    use TaskAccessor;
    use GitTaskLoader;
    use PamaldTaskLoader;

    protected string $packageManagerName = '';

    protected function getPackageManagerName(): string
    {
        assert($this->packageManagerName !== '');

        return $this->packageManagerName;
    }

    // region Option - sha1
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

    // region Option - patterns
    /**
     * @var array<string>
     */
    protected array $patterns = [];

    /**
     * @return array<string>
     */
    public function getPatterns(): array
    {
        return $this->patterns;
    }

    /**
     * @param array<string> $patterns
     */
    public function setPatterns(array $patterns): static
    {
        $this->patterns = $patterns;

        return $this;
    }
    // endregion

    // region Option - stateKeyCommitMsgParts
    protected string $stateKeyCommitMsgParts = '';

    public function getStateKeyCommitMsgParts(): string
    {
        return $this->stateKeyCommitMsgParts;
    }

    /**
     * Array key in Robo state which contains the parsed commit message parts.
     */
    public function setStateKeyCommitMsgParts(string $stateKeyCommitMsgParts): static
    {
        $this->stateKeyCommitMsgParts = $stateKeyCommitMsgParts;

        return $this;
    }
    // endregion

    /**
     * {@inheritdoc}
     *
     * @phpstan-param robo-pamald-modify-commit-msg-parts-task-options $options
     */
    public function setOptions(array $options): static
    {
        parent::setOptions($options);

        if (array_key_exists('sha1', $options)) {
            $this->setSha1($options['sha1']);
        }

        if (array_key_exists('patterns', $options)) {
            $this->setPatterns($options['patterns']);
        }

        if (array_key_exists('stateKeyCommitMsgParts', $options)) {
            $this->setStateKeyCommitMsgParts($options['stateKeyCommitMsgParts']);
        }

        return $this;
    }

    protected function runDoIt(): static
    {
        $state = $this->getState();

        $packageManagerName = $this->getPackageManagerName();
        $lockFilePaths = $this->getLockFilePaths();
        $packageCollector = $this->getPackageCollector();
        $lockDiffer = $this->getLockDiffer();
        $reporterStream = fopen('php://memory', 'w+');
        if (!$reporterStream) {
            $this->setTaskResultCode(1);
            $this->setTaskResultMessage('Unable to open memory stream for reporter.');

            return $this;
        }

        $reporter = new MarkdownTableReporter();
        $reporter->setStream($reporterStream);
        $reports = [];
        foreach ($lockFilePaths as $lockFilePath) {
            $jsonFilePath = $this->getJsonFilePath($lockFilePath);

            $refs = [
                'left' => $this->getSha1(),
                'right' => '',
            ];

            foreach ($refs as $side => $ref) {
                $filePaths = [
                    'lock' => $lockFilePath,
                    'json' => $jsonFilePath,
                ];

                foreach ($filePaths as $definitionType => $filePath) {
                    $fileContent = $this->readGitFileContent(
                        '.',
                        $ref,
                        $filePath,
                    );

                    $reports[$lockFilePath][$side][$definitionType] = $fileContent === null
                        ? null
                        : $this->unserialize($definitionType, $fileContent);
                }

                $reports[$lockFilePath][$side]['packages'] = $packageCollector->collect(
                    $reports[$lockFilePath][$side]['lock'],
                    $reports[$lockFilePath][$side]['json'],
                );
            }

            $reports[$lockFilePath]['lockDiffEntries'] = $lockDiffer->diff(
                $reports[$lockFilePath]['left']['packages'],
                $reports[$lockFilePath]['right']['packages'],
            );

            ftruncate($reporterStream, 0);
            $reporter->generate($reports[$lockFilePath]['lockDiffEntries']);
            fseek($reporterStream, 0);
            $reports[$lockFilePath]['report'] = stream_get_contents($reporterStream);
        }
        ftruncate($reporterStream, 0);
        fclose($reporterStream);

        $pattern = $this->getSubjectPattern();
        $parts = $state[$this->getStateKeyCommitMsgParts()] ?? [];
        $contentTemplate = $this->getContentTemplate();
        foreach ($parts as $index => $part) {
            $matches = [];
            if (preg_match($pattern, $part['content'], $matches) !== 1) {
                // Non-pamald part.
                continue;
            }

            $lockFilePath = $matches['lockFilePath'];
            if (!$this->isDomesticated($lockFilePath)) {
                // Pamald part, but it belongs to a foreign package manager.
                // @todo The $pattern could be more specific.
                continue;
            }

            if (!isset($reports[$lockFilePath])) {
                // This can happen with `git commit --amend`.
                // A pamald report which is not relevant anymore.
                $parts[$index]['enabled'] = false;

                continue;
            }

            $part['content'] = strtr(
                $contentTemplate,
                [
                    '{{ lockFilePath }}' => $lockFilePath,
                    '{{ report }}' => $reports[$lockFilePath]['report'],
                ],
            );
            $part['type'] = 'pamald';
            $part['pamald'] = [
                'packageManagerName' => $packageManagerName,
                'lockFilePath' => $lockFilePath,
            ];
            $parts[$index] = $part;
            unset($reports[$lockFilePath]);
        }

        $weight = isset($parts['footer_comment']['weight'])
            ? $parts['footer_comment']['weight'] - count($reports)
            : count($parts);
        foreach ($reports as $lockFilePath => $info) {
            $parts["unknown.pamald.$packageManagerName.$weight"] = [
                'enabled' => true,
                'weight' => $weight,
                'type' => 'pamald',
                'marginTop' => 1,
                'content' => strtr(
                    $contentTemplate,
                    [
                        '{{ lockFilePath }}' => $lockFilePath,
                        '{{ report }}' => $info['report'],
                    ],
                ),
                'pamald' => [
                    'packageManagerName' => $packageManagerName,
                    'lockFilePath' => $lockFilePath,
                ],
            ];
            $weight++;
        }

        $state[$this->getStateKeyCommitMsgParts()] = $parts;

        return $this;
    }

    abstract protected function getJsonFilePath(string $lockFilePath): string;

    /**
     * @return array<string, mixed>
     */
    protected function unserialize(string $type, string $fileContent): array
    {
        return json_decode($fileContent, true);
    }

    protected function getSubjectPattern(): string
    {
        return '/^Changes in (?P<lockFilePath>.+?):\n/';
    }

    protected function getContentTemplate(): string
    {
        return <<<TEXT
            Changes in {{ lockFilePath }}:
            {{ report }}
            TEXT;
    }

    abstract protected function isDomesticated(string $lockFilePath): bool;

    /**
     * @return array<string>
     */
    protected function getLockFilePaths(): array
    {
        return  array_unique(array_merge(
            $this->getLockFilePathsLeft(),
            $this->getLockFilePathsRight(),
        ));
    }

    /**
     * @return array<string>
     */
    protected function getLockFilePathsLeft(): array
    {
        // @todo Support for --git-dir.
        $ref = $this->getSha1();
        $patterns = $this->getPatterns();

        // @todo Configurable "git" executable.
        // @todo Configurable workingDir.
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

        $processHelper = $this->getProcessHelper();
        $process = $processHelper->run(
            $this->output(),
            $command,
            null,
            $this->processRunCallback(...),
        );

        // @todo ExitCode error handling.
        return array_filter(
            explode("\0", $process->getOutput()),
        );
    }

    /**
     * @return array<string>
     */
    protected function getLockFilePathsRight(): array
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

    abstract protected function getPackageCollector(): PackageCollectorInterface;

    protected function getLockDiffer(): LockDiffer
    {
        return new LockDiffer();
    }

    protected function getProcessHelper(): ProcessHelper
    {
        return $this
            ->getContainer()
            ->get('application')
            ->getHelperSet()
            ->get('process');
    }

    public function processRunCallback(string $type, string $data): void
    {
        $isStdOutputVisible = false;
        switch ($type) {
            case Process::OUT:
                // @phpstan-ignore-next-line
                if ($isStdOutputVisible) {
                    $this->output()->write($data);
                }
                break;

            case Process::ERR:
                $this->printTaskError($data);
                break;
        }
    }

    protected function readGitFileContent(
        string $workingDirectory,
        string $ref,
        string $filePath,
    ): ?string {
        $process = new Process(
            [
                'git',
                'show',
                "$ref:$filePath",
            ],
            $workingDirectory,
        );

        $exitCode = $process->run();

        return $exitCode
            ? null
            : $process->getOutput();
    }
}
