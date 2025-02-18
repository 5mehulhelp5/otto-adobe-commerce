<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing\InventorySync\Processing\Connector;

class InventoryGetItemsCommand implements \M2E\Core\Model\Connector\CommandProcessingInterface
{
    private string $accountServerHash;

    public function __construct(string $accountServerHash)
    {
        $this->accountServerHash = $accountServerHash;
    }

    public function getCommand(): array
    {
        return ['Inventory', 'Get', 'Items'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountServerHash,
        ];
    }

    public function parseResponse(
        \M2E\Core\Model\Connector\Response $response
    ): \M2E\Core\Model\Connector\Response\Processing {
        return new \M2E\Core\Model\Connector\Response\Processing($response->getResponseData()['processing_id']);
    }
}
