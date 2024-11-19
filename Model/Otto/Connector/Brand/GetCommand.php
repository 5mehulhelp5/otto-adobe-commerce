<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Brand;

class GetCommand implements \M2E\Otto\Model\Connector\CommandInterface
{
    private string $accountHash;
    private array $names;

    public function __construct(string $accountHash, array $names)
    {
        $this->accountHash = $accountHash;
        $this->names = $names;
    }

    public function getCommand(): array
    {
        return ['brand', 'get', 'entities'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'names' => $this->names,
        ];
    }

    public function parseResponse(\M2E\Otto\Model\Connector\Response $response): Get\Response
    {
        $responseData = $response->getResponseData();

        $brands = [];
        foreach ($responseData['brands'] as $brandData) {
            $brand = new Brand(
                $brandData['name'],
                $brandData['id'],
                $brandData['is_usable']
            );

            $brands[] = $brand;
        }

        return new \M2E\Otto\Model\Otto\Connector\Brand\Get\Response(
            $brands
        );
    }
}
