<?php

namespace M2E\Otto\Model\Magento\Quote;

class Builder
{
    public const PROCESS_QUOTE_ID = 'PROCESS_QUOTE_ID';

    protected \M2E\Otto\Model\Order\ProxyObject $proxyOrder;
    protected \Magento\Quote\Model\Quote $quote;
    protected \M2E\Otto\Model\Currency $currency;
    protected \Magento\Directory\Model\CurrencyFactory $magentoCurrencyFactory;
    protected \Magento\Tax\Model\Calculation $calculation;
    protected \Magento\Framework\App\Config\ReinitableConfigInterface $storeConfig;
    protected \Magento\Catalog\Model\ResourceModel\Product $productResource;
    protected Manager $quoteManager;
    protected ?Store\Configurator $storeConfigurator = null;
    protected \Magento\Sales\Model\OrderIncrementIdChecker $orderIncrementIdChecker;
    private Store\ConfiguratorFactory $configuratorFactory;
    private \M2E\Otto\Helper\Module\Exception $exceptionHelper;
    private \M2E\Otto\Helper\Data\GlobalData $globalDataHelper;
    /** @var \M2E\Otto\Model\Magento\Quote\ItemFactory */
    private ItemFactory $magentoQuoteItemFactory;

    public function __construct(
        \M2E\Otto\Model\Magento\Quote\Store\ConfiguratorFactory $configuratorFactory,
        \M2E\Otto\Model\Order\ProxyObject $proxyOrder,
        \M2E\Otto\Model\Currency $currency,
        \M2E\Otto\Model\Magento\Quote\Manager $quoteManager,
        \Magento\Directory\Model\CurrencyFactory $magentoCurrencyFactory,
        \Magento\Tax\Model\Calculation $calculation,
        \Magento\Framework\App\Config\ReinitableConfigInterface $storeConfig,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Sales\Model\OrderIncrementIdChecker $orderIncrementIdChecker,
        \M2E\Otto\Helper\Module\Exception $exceptionHelper,
        \M2E\Otto\Helper\Data\GlobalData $globalDataHelper,
        \M2E\Otto\Model\Magento\Quote\ItemFactory $magentoQuoteItemFactory
    ) {
        $this->proxyOrder = $proxyOrder;
        $this->currency = $currency;
        $this->magentoCurrencyFactory = $magentoCurrencyFactory;
        $this->calculation = $calculation;
        $this->storeConfig = $storeConfig;
        $this->productResource = $productResource;
        $this->quoteManager = $quoteManager;
        $this->orderIncrementIdChecker = $orderIncrementIdChecker;
        $this->configuratorFactory = $configuratorFactory;
        $this->exceptionHelper = $exceptionHelper;
        $this->globalDataHelper = $globalDataHelper;
        $this->magentoQuoteItemFactory = $magentoQuoteItemFactory;
    }

    public function __destruct()
    {
        if ($this->storeConfigurator === null) {
            return;
        }

        $this->storeConfigurator->restoreOriginalStoreConfigForOrder();
    }

    // ----------------------------------------

    public function build()
    {
        try {
            // do not change invoke order
            // ---------------------------------------
            $this->initializeQuote();
            $this->initializeCustomer();
            $this->initializeAddresses();

            $this->configureStore();
            $this->configureTaxCalculation();

            $this->initializeCurrency();
            $this->initializeShippingMethodData();
            $this->initializeQuoteItems();
            $this->initializePaymentMethodData();

            $this->quote = $this->quoteManager->save($this->quote);

            $this->prepareOrderNumber();

            return $this->quote;
            // ---------------------------------------
        } catch (\Throwable $e) {
            if (!isset($this->quote)) {
                $this->exceptionHelper->process($e);

                throw $e;
            }

            // Remove ordered items from customer cart
            $this->quote->setIsActive(false);
            $this->quote->removeAllAddresses();
            $this->quote->removeAllItems();

            $this->quote->save();

            throw $e;
        }
    }

    //########################################

    private function initializeQuote()
    {
        $this->quote = $this->quoteManager->getBlankQuote();

        $this->quote->setCheckoutMethod($this->proxyOrder->getCheckoutMethod());
        $this->quote->setStore($this->proxyOrder->getStore());
        $this->quote->getStore()->setData('current_currency', $this->quote->getStore()->getBaseCurrency());

        /**
         * The quote is empty at this moment, so it is not need to collect totals
         */
        $this->quote->setTotalsCollectedFlag(true);
        $this->quote = $this->quoteManager->save($this->quote);
        $this->quote->setTotalsCollectedFlag(false);

        $this->quote->setIsOttoQuote(true);
        $this->quote->setIsNeedToSendEmail($this->proxyOrder->isMagentoOrdersCustomerNewNotifyWhenOrderCreated());
        $this->quote->setNeedProcessChannelTaxes(
            $this->proxyOrder->isTaxModeChannel() ||
            ($this->proxyOrder->isTaxModeMixed() && $this->proxyOrder->hasTax())
        );

        $this->quoteManager->replaceCheckoutQuote($this->quote);

        $this->globalDataHelper->unsetValue(self::PROCESS_QUOTE_ID);
        $this->globalDataHelper->setValue(self::PROCESS_QUOTE_ID, $this->quote->getId());
    }

    //########################################

    private function initializeCustomer()
    {
        if ($this->proxyOrder->isCheckoutMethodGuest()) {
            $this->quote
                ->setCustomerId(null)
                ->setCustomerEmail($this->proxyOrder->getBuyerEmail())
                ->setCustomerFirstname($this->proxyOrder->getCustomerFirstName())
                ->setCustomerLastname($this->proxyOrder->getCustomerLastName())
                ->setCustomerIsGuest(true)
                ->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);

            return;
        }

        $this->quote->assignCustomer($this->proxyOrder->getCustomer());
    }

    //########################################

    private function initializeAddresses()
    {
        $billingAddress = $this->quote->getBillingAddress();
        $billingAddress->addData($this->proxyOrder->getBillingAddressData());

        $billingAddress->setLimitCarrier('ottoshipping');
        $billingAddress->setShippingMethod('ottoshipping_ottoshipping');
        $billingAddress->setCollectShippingRates(true);
        $billingAddress->setShouldIgnoreValidation($this->proxyOrder->shouldIgnoreBillingAddressValidation());

        $billingAddress->setCustomerAddressId(null);

        // ---------------------------------------

        $shippingAddress = $this->quote->getShippingAddress();
        $shippingAddress->setSameAsBilling(0); // maybe just set same as billing?
        $shippingAddress->addData($this->proxyOrder->getShippingAddressData());

        $shippingAddress->setLimitCarrier('ottoshipping');
        $shippingAddress->setShippingMethod('ottoshipping_ottoshipping');
        $shippingAddress->setCollectShippingRates(true);
        // ---------------------------------------
    }

    //########################################

    private function initializeCurrency()
    {
        if ($this->currency->isConvertible($this->proxyOrder->getCurrency(), $this->quote->getStore())) {
            $currentCurrency = $this->magentoCurrencyFactory->create()->load(
                $this->proxyOrder->getCurrency()
            );
        } else {
            $currentCurrency = $this->quote->getStore()->getBaseCurrency();
        }

        $this->quote->getStore()->setData('current_currency', $currentCurrency);
    }

    //########################################

    /**
     * Configure store (invoked only after address, customer and store initialization and before price calculations)
     */
    private function configureStore()
    {
        $this->storeConfigurator = $this
            ->configuratorFactory
            ->create($this->quote, $this->proxyOrder);

        $this->storeConfigurator->prepareStoreConfigForOrder();
    }

    //########################################

    private function configureTaxCalculation()
    {
        // this prevents customer session initialization (which affects cookies)
        // see Mage_Tax_Model_Calculation::getCustomer()
        $this->calculation->setCustomer($this->quote->getCustomer());
    }

    //########################################

    /**
     * @param \M2E\Otto\Model\Order\Item\ProxyObject $item
     * @param \M2E\Otto\Model\Magento\Quote\Item $quoteItemBuilder
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\DataObject $request
     *
     * @throws \M2E\Otto\Model\Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function initializeQuoteItem($item, $quoteItemBuilder, $product, $request)
    {
        // ---------------------------------------
        $productOriginalPrice = (float)$product->getPrice();

        $price = $item->getBasePrice();
        $product->setPrice($price);
        $product->setSpecialPrice($price);
        // ---------------------------------------

        $this->quote->setItemsCount($this->quote->getItemsCount() + 1);
        $this->quote->setItemsQty((float)$this->quote->getItemsQty() + $request->getQty());

        $result = $this->quote->addProduct($product, $request);
        if (is_string($result)) {
            throw new \M2E\Otto\Model\Exception($result);
        }

        $quoteItem = $this->quote->getItemByProduct($product);
        if ($quoteItem === false) {
            return;
        }

        $quoteItem->setStoreId($this->quote->getStoreId());
        $quoteItem->setOriginalCustomPrice($item->getPrice());
        $quoteItem->setOriginalPrice($productOriginalPrice);
        $quoteItem->setBaseOriginalPrice($productOriginalPrice);
        $quoteItem->setNoDiscount(1);
        foreach ($quoteItem->getChildren() as $itemChildren) {
            $itemChildren->getProduct()->setTaxClassId($quoteItem->getProduct()->getTaxClassId());
        }

        $giftMessageId = $quoteItemBuilder->getGiftMessageId();
        if (!empty($giftMessageId)) {
            $quoteItem->setGiftMessageId($giftMessageId);
        }

        $quoteItem->setAdditionalData($quoteItemBuilder->getAdditionalData($quoteItem));
    }

    /**
     * @throws \M2E\Otto\Model\Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function initializeQuoteItems()
    {
        $this->quote->setUseOttoDiscount(false);

        foreach ($this->proxyOrder->getItems() as $item) {
            $this->clearQuoteItemsCache();

            $quoteItemBuilder = $this->magentoQuoteItemFactory->create($this->quote, $item);

            $product = $quoteItemBuilder->getProduct();

            if (!$item->pretendedToBeSimple()) {
                $this->initializeQuoteItem($item, $quoteItemBuilder, $product, $quoteItemBuilder->getRequest());
                continue;
            }

            // ---------------------------------------

            $totalPrice = 0;
            $products = [];
            foreach ($product->getTypeInstance()->getAssociatedProducts($product) as $associatedProduct) {
                /** @var \Magento\Catalog\Model\Product $associatedProduct */
                if ($associatedProduct->getQty() <= 0) { // skip product if default qty zero
                    continue;
                }

                $totalPrice += $associatedProduct->getPrice();
                $products[] = $associatedProduct;
            }

            // ---------------------------------------

            foreach ($products as $associatedProduct) {
                $item->setQty($associatedProduct->getQty() * $item->getOriginalQty());

                $productPriceInSetPercent = ($associatedProduct->getPrice() / $totalPrice) * 100;
                $productPriceInItem = (($item->getOriginalPrice() * $productPriceInSetPercent) / 100);
                $item->setPrice($productPriceInItem / $associatedProduct->getQty());

                $quoteItemBuilder = $this->magentoQuoteItemFactory->create($this->quote, $item);

                $this->initializeQuoteItem(
                    $item,
                    $quoteItemBuilder,
                    $quoteItemBuilder->setTaxClassIntoProduct($associatedProduct),
                    $quoteItemBuilder->getRequest()
                );
            }
        }

        $allItems = $this->quote->getAllItems();
        $this->quote->getItemsCollection()->removeAllItems();

        foreach ($allItems as $item) {
            $item->save();
            $this->quote->getItemsCollection()->addItem($item);
        }
    }

    private function getDiscount($productPriceInItem, $associatedProductQty, $OriginalQty)
    {
        $total = 0;
        $roundPrice = round(($productPriceInItem / $associatedProductQty), 2) * $associatedProductQty;

        if ($productPriceInItem !== $roundPrice) {
            $this->quote->setUseOttoDiscount(true);
            $total = ($roundPrice - $productPriceInItem) * $OriginalQty;
        }

        return $total;
    }

    /**
     * Mage_Sales_Model_Quote_Address caches items after each collectTotals call. Some extensions calls collectTotals
     * after adding new item to quote in observers. So we need clear this cache before adding new item to quote.
     */
    private function clearQuoteItemsCache()
    {
        foreach ($this->quote->getAllAddresses() as $address) {
            $address->unsetData('cached_items_all');
            $address->unsetData('cached_items_nominal');
            $address->unsetData('cached_items_nonnominal');
        }
    }

    //########################################

    private function initializeShippingMethodData()
    {
        $this->globalDataHelper->unsetValue('shipping_data');
        $this->globalDataHelper->setValue('shipping_data', $this->proxyOrder->getShippingData());

        $this->proxyOrder->initializeShippingMethodDataPretendedToBeSimple();
    }

    //########################################

    private function initializePaymentMethodData()
    {
        $quotePayment = $this->quote->getPayment();
        $quotePayment->importData($this->proxyOrder->getPaymentData());
    }

    //########################################

    private function prepareOrderNumber()
    {
        if ($this->proxyOrder->isOrderNumberPrefixSourceChannel()) {
            $orderNumber = $this->proxyOrder->getOrderNumberPrefix() . $this->proxyOrder->getChannelOrderNumber();

            if ($this->orderIncrementIdChecker->isIncrementIdUsed($orderNumber)) {
                $orderNumber .= '(1)';
            }

            $this->quote->setReservedOrderId($orderNumber);

            return;
        }

        $orderNumber = $this->quote->getReservedOrderId();
        empty($orderNumber) && $orderNumber = $this->quote->getResource()->getReservedOrderId($this->quote);
        $orderNumber = $this->proxyOrder->getOrderNumberPrefix() . $orderNumber;

        if ($this->orderIncrementIdChecker->isIncrementIdUsed($orderNumber)) {
            $orderNumber = $this->quote->getResource()->getReservedOrderId($this->quote);
        }

        $this->quote->setReservedOrderId($orderNumber);
    }
}
