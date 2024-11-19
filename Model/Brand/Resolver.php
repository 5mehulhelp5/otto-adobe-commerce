<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Brand;

class Resolver
{
    private \M2E\Otto\Model\Otto\Connector\Brand\Get\Processor $brandProcessor;
    private \M2E\Otto\Model\BrandFactory $brandFactory;
    private \M2E\Otto\Model\Brand\Repository $brandRepository;

    public function __construct(
        \M2E\Otto\Model\Otto\Connector\Brand\Get\Processor $brandProcessor,
        \M2E\Otto\Model\BrandFactory $brandFactory,
        \M2E\Otto\Model\Brand\Repository $brandRepository
    ) {
        $this->brandProcessor = $brandProcessor;
        $this->brandFactory = $brandFactory;
        $this->brandRepository = $brandRepository;
    }

    /**
     * @return \M2E\Otto\Model\Brand[]
     */
    public function resolveByBrandNames(\M2E\Otto\Model\Account $account, array $names): array
    {
        $brands = $this->brandRepository->findByBrandNames($names);

        $nonExistentBrands = array_diff($names, array_map(
            static function (\M2E\Otto\Model\Brand $brand) {
                return $brand->getName();
            },
            $brands,
        ));

        if (!empty($nonExistentBrands)) {
            $brands = array_merge($brands, $this->getBrandsFromServer($account, $nonExistentBrands));
        }

        return $brands;
    }

    public function resolveByBrandName(\M2E\Otto\Model\Account $account, string $brandName): ?\M2E\Otto\Model\Brand
    {
        $result = $this->resolveByBrandNames($account, [$brandName]);
        if (empty($result)) {
            return null;
        }

        return reset($result);
    }

    /**
     * @return \M2E\Otto\Model\Brand[]
     */
    public function getBrandsFromServer(\M2E\Otto\Model\Account $account, array $names): array
    {
        $response = $this->brandProcessor->process($account, $names);

        $brands = [];
        foreach ($response->getBrands() as $brand) {
            $brands[] = $this->brandFactory->create()->init(
                $brand->getName(),
                $brand->getBrandId(),
                $brand->isUsable(),
            );
        }
        $this->brandRepository->batchInsert($brands);

        return $brands;
    }
}
