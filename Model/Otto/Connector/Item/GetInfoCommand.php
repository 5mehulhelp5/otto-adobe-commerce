<?php

namespace M2E\Otto\Model\Otto\Connector\Item;

class GetInfoCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $ottoProductSku;
    private string $accountHash;

    public function __construct(string $ottoProductSku, string $accountHash)
    {
        $this->ottoProductSku = $ottoProductSku;
        $this->accountHash = $accountHash;
    }

    public function getCommand(): array
    {
        return ['inventory', 'get', 'items'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'product_sku' => $this->ottoProductSku,
        ];
    }

    public function parseResponse(
        \M2E\Core\Model\Connector\Response $response
    ): \M2E\Core\Model\Connector\Response {
        return $response;
    }
}
