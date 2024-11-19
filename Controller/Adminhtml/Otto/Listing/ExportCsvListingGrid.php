<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Listing;

class ExportCsvListingGrid extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractMain
{
    private \M2E\Otto\Helper\Data\FileExport $fileExportHelper;
    private \M2E\Otto\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\Otto\Model\Listing\Repository $listingRepository,
        \M2E\Otto\Helper\Data\FileExport $fileExportHelper
    ) {
        parent::__construct();

        $this->fileExportHelper = $fileExportHelper;
        $this->listingRepository = $listingRepository;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $listing = $this->listingRepository->get((int)$id);

        $gridName = $listing->getTitle();

        $content = $this->_view
            ->getLayout()
            ->createBlock(
                \M2E\Otto\Block\Adminhtml\Otto\Listing\View\Otto\Grid::class,
                '',
                ['data' => ['listing' => $listing]],
            )
            ->getCsv();

        return $this->fileExportHelper->createFile($gridName, $content);
    }
}
