<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Magento\Product\Rule\Condition;

class Combine extends \M2E\Otto\Model\Magento\Product\Rule\Condition\Combine
{
    public function __construct(
        \M2E\Otto\Model\Magento\Product\Rule\Condition\ProductFactory $ruleConditionProductFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Rule\Model\Condition\Context $context,
        array $data = []
    ) {
        parent::__construct($ruleConditionProductFactory, $objectManager, $context, $data);
        $this->setType('Otto_Magento_Product_Rule_Condition_Combine');
    }

    /**
     * @return string
     */
    protected function getConditionCombine()
    {
        return $this->getType() . '|otto|';
    }

    /**
     * @return string
     */
    protected function getCustomLabel()
    {
        return (string)__('Otto Values');
    }

    /**
     * @return array
     */
    protected function getCustomOptions()
    {
        $attributes = $this->getCustomOptionsAttributes();

        return !empty($attributes) ?
            $this->getOptions('Otto_Magento_Product_Rule_Condition_Product', $attributes, ['otto'])
            : [];
    }
}
