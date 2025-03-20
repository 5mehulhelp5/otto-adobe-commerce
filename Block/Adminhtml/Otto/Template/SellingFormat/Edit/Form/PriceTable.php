<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Template\SellingFormat\Edit\Form;

use M2E\Otto\Block\Adminhtml\Magento\AbstractBlock;
use M2E\Otto\Model\Template\SellingFormat;

class PriceTable extends AbstractBlock
{
    protected $_template = 'otto/template/selling_format/price_table.phtml';

    protected \Magento\Framework\Locale\CurrencyInterface $currency;

    public \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper;
    private \M2E\Otto\Helper\Data\GlobalData $globalDataHelper;

    public function __construct(
        \M2E\Otto\Helper\Data\GlobalData $globalDataHelper,
        \Magento\Framework\Locale\CurrencyInterface $currency,
        \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        $this->globalDataHelper = $globalDataHelper;
        $this->currency = $currency;
        $this->magentoAttributeHelper = $magentoAttributeHelper;
        parent::__construct($context, $data);
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $buttonBlock = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Magento\Button::class)
                            ->setData([
                                'label' => __('Add Price Change'),
                                'onclick' => 'OttoTemplateSellingFormatObj.addFixedPriceChangeRow();',
                                'class' => 'action primary',
                            ]);
        $this->setChild('add_fixed_price_change_button', $buttonBlock);
    }

    /**
     * @param string $fixedPriceModifierString
     *
     * @return array
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getFixedPriceModifierAttributes($fixedPriceModifierString)
    {
        $fixedPriceModifier = \M2E\Core\Helper\Json::decode($fixedPriceModifierString);
        if (!is_array($fixedPriceModifier) || empty($fixedPriceModifier)) {
            return [];
        }

        $result = [];
        foreach ($fixedPriceModifier as $modification) {
            if (
                $modification['mode'] == SellingFormat::PRICE_COEFFICIENT_ATTRIBUTE
                && $modification['attribute_code']
            ) {
                $result[] = $modification['attribute_code'];
            }
        }

        return $result;
    }

    public function getAttributes()
    {
        return $this->globalDataHelper->getValue('otto_attributes');
    }
}
