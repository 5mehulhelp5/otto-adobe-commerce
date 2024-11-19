<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing;

use M2E\Otto\Model\ResourceModel\Listing as ListingResource;
use M2E\Otto\Model\Template\SellingFormat;
use M2E\Otto\Model\Template\Synchronization;
use M2E\Otto\Model\Template\Description;
use M2E\Otto\Model\Template\Shipping;

class UpdateService
{
    private Description\Repository $descriptionTemplateRepository;
    private Description\SnapshotBuilderFactory $descriptionSnapshotBuilderFactory;
    private Description\DiffFactory $descriptionDiffFactory;
    private Description\ChangeProcessorFactory $descriptionChangeProcessorFactory;
    private \M2E\Otto\Model\Otto\Listing\SnapshotBuilderFactory $listingSnapshotBuilderFactory;
    private \M2E\Otto\Model\Listing\Repository $listingRepository;
    private \M2E\Otto\Model\Otto\Listing\AffectedListingsProductsFactory $affectedListingsProductsFactory;
    private SellingFormat\Repository $sellingFormatTemplateRepository;
    private SellingFormat\SnapshotBuilderFactory $sellingFormatSnapshotBuilderFactory;
    private SellingFormat\DiffFactory $sellingFormatDiffFactory;
    private SellingFormat\ChangeProcessorFactory $sellingFormatChangeProcessorFactory;
    private Synchronization\Repository $synchronizationTemplateRepository;
    private Synchronization\SnapshotBuilderFactory $synchronizationSnapshotBuilderFactory;
    private Synchronization\DiffFactory $synchronizationDiffFactory;
    private Synchronization\ChangeProcessorFactory $synchronizationChangeProcessorFactory;
    private Shipping\SnapshotBuilderFactory $shippingSnapshotBuilderFactory;
    private Shipping\ChangeProcessorFactory $shippingChangeProcessorFactory;
    private \M2E\Otto\Model\Template\Shipping\ShippingDiffStub $shippingDiffStub;

    public function __construct(
        \M2E\Otto\Model\Listing\Repository $listingRepository,
        \M2E\Otto\Model\Otto\Listing\SnapshotBuilderFactory $listingSnapshotBuilderFactory,
        \M2E\Otto\Model\Otto\Listing\AffectedListingsProductsFactory $affectedListingsProductsFactory,
        Description\Repository $descriptionTemplateRepository,
        Description\SnapshotBuilderFactory $descriptionSnapshotBuilderFactory,
        Description\DiffFactory $descriptionDiffFactory,
        Description\ChangeProcessorFactory $descriptionChangeProcessorFactory,
        SellingFormat\Repository $sellingFormatTemplateRepository,
        SellingFormat\SnapshotBuilderFactory $sellingFormatSnapshotBuilderFactory,
        SellingFormat\DiffFactory $sellingFormatDiffFactory,
        SellingFormat\ChangeProcessorFactory $sellingFormatChangeProcessorFactory,
        Synchronization\Repository $synchronizationTemplateRepository,
        Synchronization\SnapshotBuilderFactory $synchronizationSnapshotBuilderFactory,
        Synchronization\DiffFactory $synchronizationDiffFactory,
        Synchronization\ChangeProcessorFactory $synchronizationChangeProcessorFactory,
        Shipping\SnapshotBuilderFactory $shippingSnapshotBuilderFactory,
        Shipping\ChangeProcessorFactory $shippingChangeProcessorFactory,
        \M2E\Otto\Model\Template\Shipping\ShippingDiffStub $shippingDiffStub
    ) {
        $this->shippingDiffStub = $shippingDiffStub;
        $this->shippingChangeProcessorFactory = $shippingChangeProcessorFactory;
        $this->shippingSnapshotBuilderFactory = $shippingSnapshotBuilderFactory;
        $this->descriptionTemplateRepository = $descriptionTemplateRepository;
        $this->descriptionSnapshotBuilderFactory = $descriptionSnapshotBuilderFactory;
        $this->descriptionDiffFactory = $descriptionDiffFactory;
        $this->descriptionChangeProcessorFactory = $descriptionChangeProcessorFactory;
        $this->listingSnapshotBuilderFactory = $listingSnapshotBuilderFactory;
        $this->listingRepository = $listingRepository;
        $this->affectedListingsProductsFactory = $affectedListingsProductsFactory;
        $this->sellingFormatTemplateRepository = $sellingFormatTemplateRepository;
        $this->sellingFormatSnapshotBuilderFactory = $sellingFormatSnapshotBuilderFactory;
        $this->sellingFormatDiffFactory = $sellingFormatDiffFactory;
        $this->sellingFormatChangeProcessorFactory = $sellingFormatChangeProcessorFactory;
        $this->synchronizationTemplateRepository = $synchronizationTemplateRepository;
        $this->synchronizationSnapshotBuilderFactory = $synchronizationSnapshotBuilderFactory;
        $this->synchronizationDiffFactory = $synchronizationDiffFactory;
        $this->synchronizationChangeProcessorFactory = $synchronizationChangeProcessorFactory;
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function update(\M2E\Otto\Model\Listing $listing, array $post)
    {
        $isNeedProcessChangesDescriptionTemplate = false;
        $isNeedProcessChangesSellingFormatTemplate = false;
        $isNeedProcessChangesSynchronizationTemplate = false;
        $isNeedProcessChangesShippingTemplate = false;

        $oldListingSnapshot = $this->makeListingSnapshot($listing);

        $newTemplateDescriptionId = $post[ListingResource::COLUMN_TEMPLATE_DESCRIPTION_ID] ?? null;
        if (
            $newTemplateDescriptionId !== null
            && $listing->getTemplateDescriptionId() !== (int)$newTemplateDescriptionId
        ) {
            $listing->setTemplateDescriptionId((int)$newTemplateDescriptionId);
            $isNeedProcessChangesDescriptionTemplate = true;
        }

        $newTemplateSellingFormatId = $post[ListingResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID] ?? null;
        if (
            $newTemplateSellingFormatId !== null
            && $listing->getTemplateSellingFormatId() !== (int)$newTemplateSellingFormatId
        ) {
            $listing->setTemplateSellingFormatId((int)$newTemplateSellingFormatId);
            $isNeedProcessChangesSellingFormatTemplate = true;
        }

        $newTemplateSynchronizationId = $post[ListingResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID] ?? null;
        if (
            $newTemplateSynchronizationId !== null
            && $listing->getTemplateSynchronizationId() !== (int)$newTemplateSynchronizationId
        ) {
            $listing->setTemplateSynchronizationId((int)$newTemplateSynchronizationId);
            $isNeedProcessChangesSynchronizationTemplate = true;
        }

        $newTemplateShippingId = $post[ListingResource::COLUMN_TEMPLATE_SHIPPING_ID] ?? null;
        if (
            $newTemplateShippingId !== null
            && $listing->getTemplateShippingId() !== (int)$newTemplateShippingId
        ) {
            $listing->setTemplateShippingId((int)$newTemplateShippingId);
            $isNeedProcessChangesShippingTemplate = true;
        }

        if (
            $isNeedProcessChangesDescriptionTemplate === false
            && $isNeedProcessChangesSellingFormatTemplate === false
            && $isNeedProcessChangesSynchronizationTemplate === false
            && $isNeedProcessChangesShippingTemplate === false
        ) {
            return;
        }

        $this->listingRepository->save($listing);

        $newListingSnapshot = $this->makeListingSnapshot($listing);

        $affectedListingsProducts = $this->affectedListingsProductsFactory->create();
        $affectedListingsProducts->setModel($listing);

        if ($isNeedProcessChangesSellingFormatTemplate) {
            $this->processChangeSellingFormatTemplate(
                (int)$oldListingSnapshot[ListingResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID],
                (int)$newListingSnapshot[ListingResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID],
                $affectedListingsProducts
            );
        }

        if ($isNeedProcessChangesDescriptionTemplate) {
            $this->processChangeDescriptionTemplate(
                (int)$oldListingSnapshot[ListingResource::COLUMN_TEMPLATE_DESCRIPTION_ID],
                (int)$newListingSnapshot[ListingResource::COLUMN_TEMPLATE_DESCRIPTION_ID],
                $affectedListingsProducts
            );
        }

        if ($isNeedProcessChangesSynchronizationTemplate) {
            $this->processChangeSynchronizationTemplate(
                (int)$oldListingSnapshot[ListingResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID],
                (int)$newListingSnapshot[ListingResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID],
                $affectedListingsProducts
            );
        }

        if ($isNeedProcessChangesShippingTemplate) {
            $this->processChangeShippingTemplate(
                $affectedListingsProducts
            );
        }
    }

    private function makeListingSnapshot(\M2E\Otto\Model\Listing $listing)
    {
        $snapshotBuilder = $this->listingSnapshotBuilderFactory->create();
        $snapshotBuilder->setModel($listing);

        return $snapshotBuilder->getSnapshot();
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    private function processChangeSellingFormatTemplate(
        int $oldId,
        int $newId,
        \M2E\Otto\Model\Otto\Listing\AffectedListingsProducts $affectedListingsProducts
    ) {
        $oldTemplate = $this->sellingFormatTemplateRepository->get($oldId);
        $newTemplate = $this->sellingFormatTemplateRepository->get($newId);

        $oldTemplateData = $this->makeSellingFormatTemplateSnapshot($oldTemplate);
        $newTemplateData = $this->makeSellingFormatTemplateSnapshot($newTemplate);

        $diff = $this->sellingFormatDiffFactory->create();
        $diff->setOldSnapshot($oldTemplateData);
        $diff->setNewSnapshot($newTemplateData);

        $changeProcessor = $this->sellingFormatChangeProcessorFactory->create();

        $affectedProducts = $affectedListingsProducts->getObjectsData(
            ['id', 'status'],
            ['template' => \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_SELLING_FORMAT]
        );
        $changeProcessor->process($diff, $affectedProducts);
    }

    private function makeSellingFormatTemplateSnapshot(SellingFormat $sellingFormatTemplate)
    {
        $snapshotBuilder = $this->sellingFormatSnapshotBuilderFactory->create();
        $snapshotBuilder->setModel($sellingFormatTemplate);

        return $snapshotBuilder->getSnapshot();
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    private function processChangeSynchronizationTemplate(
        int $oldId,
        int $newId,
        \M2E\Otto\Model\Otto\Listing\AffectedListingsProducts $affectedListingsProducts
    ) {
        $oldTemplate = $this->synchronizationTemplateRepository->get($oldId);
        $newTemplate = $this->synchronizationTemplateRepository->get($newId);

        $oldTemplateData = $this->makeSynchronizationTemplateSnapshot($oldTemplate);
        $newTemplateData = $this->makeSynchronizationTemplateSnapshot($newTemplate);

        $diff = $this->synchronizationDiffFactory->create();
        $diff->setOldSnapshot($oldTemplateData);
        $diff->setNewSnapshot($newTemplateData);

        $changeProcessor = $this->synchronizationChangeProcessorFactory->create();

        $affectedProducts = $affectedListingsProducts->getObjectsData(
            ['id', 'status'],
            ['template' => \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_SYNCHRONIZATION]
        );
        $changeProcessor->process($diff, $affectedProducts);
    }

    private function makeSynchronizationTemplateSnapshot(Synchronization $synchronizationTemplate)
    {
        $snapshotBuilder = $this->synchronizationSnapshotBuilderFactory->create();
        $snapshotBuilder->setModel($synchronizationTemplate);

        return $snapshotBuilder->getSnapshot();
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    private function processChangeDescriptionTemplate(
        int $oldId,
        int $newId,
        \M2E\Otto\Model\Otto\Listing\AffectedListingsProducts $affectedListingsProducts
    ) {
        $oldTemplate = $this->descriptionTemplateRepository->get($oldId);
        $newTemplate = $this->descriptionTemplateRepository->get($newId);

        $oldTemplateData = $this->makeDescriptionTemplateSnapshot($oldTemplate);
        $newTemplateData = $this->makeDescriptionTemplateSnapshot($newTemplate);

        $diff = $this->descriptionDiffFactory->create();
        $diff->setOldSnapshot($oldTemplateData);
        $diff->setNewSnapshot($newTemplateData);

        $changeProcessor = $this->descriptionChangeProcessorFactory->create();

        $affectedProducts = $affectedListingsProducts->getObjectsData(
            ['id', 'status'],
            ['template' => \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_DESCRIPTION]
        );
        $changeProcessor->process($diff, $affectedProducts);
    }

    private function makeDescriptionTemplateSnapshot(Description $descriptionTemplate)
    {
        $snapshotBuilder = $this->descriptionSnapshotBuilderFactory->create();
        $snapshotBuilder->setModel($descriptionTemplate);

        return $snapshotBuilder->getSnapshot();
    }

    private function processChangeShippingTemplate(
        \M2E\Otto\Model\Otto\Listing\AffectedListingsProducts $affectedListingsProducts
    ): void {
        $changeProcessor = $this->shippingChangeProcessorFactory->create();

        $affectedProducts = $affectedListingsProducts->getObjectsData(
            ['id', 'status'],
            ['template' => \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_SHIPPING]
        );
        $changeProcessor->process($this->shippingDiffStub, $affectedProducts);
    }

    private function makeShippingTemplateSnapshot(Shipping $shippingTemplate)
    {
        $snapshotBuilder = $this->shippingSnapshotBuilderFactory->create();
        $snapshotBuilder->setModel($shippingTemplate);

        return $snapshotBuilder->getSnapshot();
    }
}
