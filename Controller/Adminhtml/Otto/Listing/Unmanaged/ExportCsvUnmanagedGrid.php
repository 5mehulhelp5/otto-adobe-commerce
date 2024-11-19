<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Listing\Unmanaged;

class ExportCsvUnmanagedGrid extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractMain
{
    private \M2E\Otto\Helper\Data\FileExport $fileExportHelper;

    public function __construct(
        \M2E\Otto\Helper\Data\FileExport $fileExportHelper
    ) {
        parent::__construct();

        $this->fileExportHelper = $fileExportHelper;
    }

    public function execute()
    {
        $gridName = \M2E\Otto\Helper\Data\FileExport::UNMANAGED_GRID;

        $content = $this
            ->getLayout()
            ->createBlock(\M2E\Otto\Block\Adminhtml\Otto\Listing\Unmanaged\Grid::class)
            ->getCsv();

        return $this->fileExportHelper->createFile($gridName, $content);
    }
}
