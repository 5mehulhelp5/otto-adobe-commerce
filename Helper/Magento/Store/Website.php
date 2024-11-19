<?php

declare(strict_types=1);

namespace M2E\Otto\Helper\Magento\Store;

class Website
{
    private \Magento\Store\Api\Data\WebsiteInterface $defaultWebsite;
    private \Magento\Store\Model\WebsiteFactory $websiteFactory;
    private \Magento\Store\Model\StoreManagerInterface $storeManager;

    public function __construct(
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->websiteFactory = $websiteFactory;
        $this->storeManager = $storeManager;
    }

    public function isExists($entity)
    {
        if ($entity instanceof \Magento\Store\Model\Website) {
            return (bool)$entity->getCode();
        }

        try {
            $this->storeManager->getWebsite($entity);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public function getName($storeId)
    {
        $website = $this->getWebsite($storeId);

        return $website ? $website->getName() : '';
    }

    /**
     * @return \Magento\Store\Api\Data\WebsiteInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDefault()
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->defaultWebsite)) {
            return $this->defaultWebsite;
        }

        $this->defaultWebsite = $this->storeManager->getWebsite(true);

        return $this->defaultWebsite;
    }

    public function getDefaultId()
    {
        return (int)$this->getDefault()->getId();
    }

    public function getWebsite($storeId)
    {
        try {
            $store = $this->storeManager->getStore($storeId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }

        return $this->storeManager->getWebsite($store->getWebsiteId());
    }

    public function getWebsites($withDefault = false)
    {
        return $this->storeManager->getWebsites($withDefault);
    }

    public function addWebsite($name, $code)
    {
        $website = $this->websiteFactory->create()->load($code, 'code');

        if ($website->getId()) {
            $error = (string)__('Website with code %value already exists', ['value' => $code]);
            throw new \M2E\Otto\Model\Exception($error);
        }

        $website = $this->websiteFactory->create();

        $website->setCode($code);
        $website->setName($name);
        $website->setId(null)->save();

        return $website;
    }
}
