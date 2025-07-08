<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Dashboard\ProductIssues;

class DataProvider implements \M2E\Core\Model\Dashboard\ProductIssues\DataProviderInterface
{
    private const TOP_ISSUES_LIMIT = 5;

    private \M2E\Otto\Model\Tag\ListingProduct\Repository $repository;
    private \M2E\Otto\Model\Dashboard\ProductIssues\Cache $cache;

    public function __construct(
        \M2E\Otto\Model\Tag\ListingProduct\Repository $repository,
        \M2E\Otto\Model\Dashboard\ProductIssues\Cache $cache
    ) {
        $this->repository = $repository;
        $this->cache = $cache;
    }

    public function getTopIssues(): array
    {
        return $this->cache->getCachedIssues(__METHOD__, function () {
            return $this->repository->getTopIssues(self::TOP_ISSUES_LIMIT);
        });
    }
}
