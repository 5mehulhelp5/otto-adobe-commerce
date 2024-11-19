<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ControlPanel\Inspection\Inspector;

use M2E\Otto\Model\ControlPanel\Inspection\InspectorInterface;
use M2E\Otto\Helper\Factory as HelperFactory;
use M2E\Otto\Model\ControlPanel\Inspection\Issue\Factory as IssueFactory;

class ShowModuleLoggers implements InspectorInterface
{
    private array $loggers = [];

    private IssueFactory $issueFactory;
    private \M2E\Otto\Helper\Client $clientHelper;

    public function __construct(
        IssueFactory $issueFactory,
        \M2E\Otto\Helper\Client $clientHelper
    ) {
        $this->issueFactory = $issueFactory;
        $this->clientHelper = $clientHelper;
    }

    public function process(): array
    {
        $issues = [];
        $this->searchLoggers();

        if (!empty($this->loggers)) {
            $issues[] = $this->issueFactory->create(
                'Otto loggers were found in magento files',
                $this->loggers,
            );
        }

        return $issues;
    }

    private function searchLoggers(): void
    {
        $recursiveIteratorIterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $this->clientHelper->getBaseDirectory() . 'vendor',
                \FilesystemIterator::FOLLOW_SYMLINKS,
            ),
        );

        foreach ($recursiveIteratorIterator as $splFileInfo) {
            /**@var \SplFileInfo $splFileInfo */

            if (
                !$splFileInfo->isFile()
                || !in_array($splFileInfo->getExtension(), ['php', 'phtml'])
            ) {
                continue;
            }

            if (strpos($splFileInfo->getRealPath(), 'M2E' . DIRECTORY_SEPARATOR . 'Otto') !== false) {
                continue;
            }

            $splFileObject = $splFileInfo->openFile();
            if (!$splFileObject->getSize()) {
                continue;
            }

            $content = $splFileObject->fread($splFileObject->getSize());
            if (strpos($content, 'Module\Logger') === false) {
                continue;
            }

            $content = explode("\n", $content);
            foreach ($content as $line => $contentRow) {
                if (strpos($contentRow, 'Module\Logger') === false) {
                    continue;
                }

                $this->loggers[] = $splFileObject->getRealPath() . ' in line ' . $line;
            }
        }
    }
}
