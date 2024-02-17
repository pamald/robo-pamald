<?php

declare(strict_types = 1);

namespace Pamald\Robo\Pamald;

use Pamald\Robo\PamaldComposer\PamaldComposerTaskLoader;
use Pamald\Robo\PamaldYarn\PamaldYarnTaskLoader;
use Robo\Collection\CallableTask;
use Robo\Collection\CollectionBuilder;
use Robo\Collection\Tasks as CollectionTaskLoader;
use Robo\Contract\TaskInterface;
use Robo\State\Data as RoboState;

trait PrepareCommitMsgTrait
{
    use CollectionTaskLoader;
    use PrepareCommitMsgTaskLoader;
    use PamaldComposerTaskLoader;
    use PamaldYarnTaskLoader;
    use PamaldTaskLoader;

    /**
     * @return array<string, \Robo\Contract\TaskInterface>
     */
    protected function getGitHookPrepareCommitMsgTaskList(
        CollectionBuilder $cb,
        string $commitMsgFilePath,
        string $messageSource = '',
        string $sha1 = '',
        string $anp = '',
        string $ans = '',
    ): array {
        return [
            'init.inputs' => $this
                ->taskPamaldPrepareCommitMsgInitInputs()
                ->setFilePath($commitMsgFilePath)
                ->setMessageSource($messageSource)
                ->setSha1($sha1)
                ->setAssetNamePrefix($anp)
                ->setAssetNameSuffix($ans),
            'init.tools' => $this
                ->taskPamaldPrepareCommitMsgInitTools()
                ->setAssetNamePrefix($anp)
                ->setAssetNameSuffix($ans),
            'file.parse' => $this
                ->taskPamaldPrepareCommitMsgParse()
                ->setAssetNamePrefix($anp)
                ->setAssetNameSuffix($ans)
                ->deferTaskConfiguration('setFilePath', "{$anp}commitMsg.filePath{$ans}")
                ->deferTaskConfiguration('setHandler', "{$anp}commitMsg.handler{$ans}")
                ->deferTaskConfiguration('setModifiers', "{$anp}commitMsg.handler.partsModifiers{$ans}"),
            'parts.render' => $this->getGitHookPrepareCommitMsgTaskRender($cb, $anp, $ans),
            'file.write' => $this->getGitHookPrepareCommitMsgTaskWrite($cb, $anp, $ans),
        ];
    }

    protected function getGitHookPrepareCommitMsgTaskRender(
        CollectionBuilder $cb,
        string $assetNamePrefix,
        string $assetNameSuffix,
    ): TaskInterface {
        return new CallableTask(
            function (RoboState $state) use ($assetNamePrefix, $assetNameSuffix): int {
                $keyContent = "{$assetNamePrefix}commitMsg.content{$assetNameSuffix}";
                $keyHandler = "{$assetNamePrefix}commitMsg.handler{$assetNameSuffix}";
                $keyParts = "{$assetNamePrefix}commitMsg.parts{$assetNameSuffix}";
                $state[$keyContent] = $state[$keyHandler]->render($state[$keyParts]);

                return 0;
            },
            $cb,
        );
    }

    protected function getGitHookPrepareCommitMsgTaskWrite(
        CollectionBuilder $cb,
        string $assetNamePrefix,
        string $assetNameSuffix,
    ): TaskInterface {
        return $this
            ->taskWriteToFile('')
            ->deferTaskConfiguration('filename', "{$assetNamePrefix}commitMsg.filePath{$assetNameSuffix}")
            ->deferTaskConfiguration('text', "{$assetNamePrefix}commitMsg.content{$assetNameSuffix}");
    }
}
