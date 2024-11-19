<?php

namespace M2E\Otto\Model\Product;

class QtyCalculator
{
    /**
     * @var null|array
     */
    private $source = null;

    private \M2E\Otto\Model\ProductInterface $product;

    /**
     * @var null|int
     */
    private $productValueCache = null;
    private \M2E\Otto\Helper\Module\Configuration $moduleConfiguration;

    private bool $isMagentoMode = false;

    public function __construct(
        \M2E\Otto\Model\ProductInterface $product,
        \M2E\Otto\Helper\Module\Configuration $moduleConfiguration
    ) {
        $this->product = $product;
        $this->moduleConfiguration = $moduleConfiguration;
    }

    // ----------------------------------------

    public function setIsMagentoMode(bool $value): self
    {
        $this->isMagentoMode = $value;

        return $this;
    }

    private function getIsMagentoMode(): bool
    {
        return $this->isMagentoMode;
    }

    // ---------------------------------------

    private function getSellingFormatTemplate(): \M2E\Otto\Model\Template\SellingFormat
    {
        return $this->product->getSellingFormatTemplate();
    }

    // ---------------------------------------

    /**
     * @param null|string $key
     *
     * @return array|mixed
     */
    private function getSource($key = null)
    {
        if ($this->source === null) {
            $this->source = $this->getSellingFormatTemplate()->getQtySource();
        }

        return ($key !== null && isset($this->source[$key])) ?
            $this->source[$key] : $this->source;
    }

    private function getMagentoProduct(): \M2E\Otto\Model\Magento\Product\Cache
    {
        return $this->product->getMagentoProduct();
    }

    //########################################

    public function getProductValue()
    {
        if ($this->getIsMagentoMode()) {
            return (int)$this->getMagentoProduct()->getQty(true);
        }

        if ($this->productValueCache !== null) {
            return $this->productValueCache;
        }

        $value = $this->getClearProductValue();

        $value = $this->applySellingFormatTemplateModifications($value);
        if ($value < 0) {
            $value = 0;
        }

        return $this->productValueCache = (int)floor($value);
    }

    private function getClearProductValue()
    {
        switch ($this->getSource('mode')) {
            case \M2E\Otto\Model\Template\SellingFormat::QTY_MODE_NUMBER:
                $value = (int)$this->getSource('value');
                break;

            case \M2E\Otto\Model\Template\SellingFormat::QTY_MODE_ATTRIBUTE:
                $value = (int)$this->getMagentoProduct()->getAttributeValue($this->getSource('attribute'));
                break;

            case \M2E\Otto\Model\Template\SellingFormat::QTY_MODE_PRODUCT_FIXED:
                $value = (int)$this->getMagentoProduct()->getQty(false);
                break;

            case \M2E\Otto\Model\Template\SellingFormat::QTY_MODE_PRODUCT:
                $value = (int)$this->getMagentoProduct()->getQty(true);
                break;

            default:
                throw new \M2E\Otto\Model\Exception\Logic('Unknown Mode in Database.');
        }

        return $value;
    }

    private function applySellingFormatTemplateModifications($value)
    {
        if ($this->getIsMagentoMode()) {
            return $value;
        }

        $mode = $this->getSource('mode');

        if (
            $mode != \M2E\Otto\Model\Template\SellingFormat::QTY_MODE_ATTRIBUTE &&
            $mode != \M2E\Otto\Model\Template\SellingFormat::QTY_MODE_PRODUCT_FIXED &&
            $mode != \M2E\Otto\Model\Template\SellingFormat::QTY_MODE_PRODUCT
        ) {
            return $value;
        }

        $value = $this->applyValuePercentageModifications($value);
        $value = $this->applyValueMinMaxModifications($value);

        return $value;
    }

    // ---------------------------------------

    private function applyValuePercentageModifications($value)
    {
        $percents = $this->getSource('qty_percentage');

        if ($value <= 0 || $percents < 0 || $percents == 100) {
            return $value;
        }

        $roundingFunction = $this->moduleConfiguration->getQtyPercentageRoundingGreater() ? 'ceil' : 'floor';

        return (int)$roundingFunction(($value / 100) * $percents);
    }

    private function applyValueMinMaxModifications($value)
    {
        if ($value <= 0 || !$this->getSource('qty_modification_mode')) {
            return $value;
        }

        $minValue = $this->getSource('qty_min_posted_value');
        $value < $minValue && $value = 0;

        $maxValue = $this->getSource('qty_max_posted_value');
        $value > $maxValue && $value = $maxValue;

        return $value;
    }
}
