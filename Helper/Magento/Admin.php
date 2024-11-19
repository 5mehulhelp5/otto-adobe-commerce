<?php

declare(strict_types=1);

namespace M2E\Otto\Helper\Magento;

class Admin extends AbstractHelper
{
    private \Magento\User\Model\User $user;
    private \Magento\Store\Model\StoreManagerInterface $storeManager;
    private \Magento\Backend\Model\Auth\Session $authSession;
    private \M2E\Otto\Helper\Magento\Store $magentoStore;

    public function __construct(
        \M2E\Otto\Helper\Magento\Store $magentoStore,
        \Magento\User\Model\User $user,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($objectManager);
        $this->user = $user;
        $this->storeManager = $storeManager;
        $this->authSession = $authSession;
        $this->magentoStore = $magentoStore;
    }

    /**
     * @return array|mixed|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentInfo()
    {
        $defaultStoreId = $this->magentoStore->getDefaultStoreId();

        $userId = $this->authSession->getUser()->getId();
        $userInfo = $this->user->load($userId)->getData();

        $tempPath = defined('Mage_Shipping_Model_Config::XML_PATH_ORIGIN_CITY')
            ? \Magento\Shipping\Model\Config::XML_PATH_ORIGIN_CITY : 'shipping/origin/city';
        $userInfo['city'] = $this->storeManager->getStore($defaultStoreId)->getConfig($tempPath);

        $tempPath = defined('Mage_Shipping_Model_Config::XML_PATH_ORIGIN_POSTCODE')
            ? \Magento\Shipping\Model\Config::XML_PATH_ORIGIN_POSTCODE : 'shipping/origin/postcode';
        $userInfo['postal_code'] = $this->storeManager->getStore($defaultStoreId)->getConfig($tempPath);

        $userInfo['country'] = $this->storeManager->getStore($defaultStoreId)->getConfig('general/country/default');

        $requiredKeys = [
            'email',
            'firstname',
            'lastname',
            'country',
            'city',
            'postal_code',
        ];

        foreach ($userInfo as $key => $value) {
            if (!in_array($key, $requiredKeys)) {
                unset($userInfo[$key]);
            }
        }

        return $userInfo;
    }
}
