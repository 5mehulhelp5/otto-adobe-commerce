<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Listing\Other\Grid;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection implements
    \Magento\Framework\Api\Search\SearchResultInterface
{
    use \M2E\Otto\Model\ResourceModel\SearchResultTrait;

    protected $_idFieldName = 'id';

    public function _construct(): void
    {
        $this->_init(
            \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
            \M2E\Otto\Model\ResourceModel\Listing\Other::class,
        );
    }

    /**
     * @psalm-suppress ParamNameMismatch
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'account') {
            $field = 'account_id';
        }

        if ($field === 'linked') {
            $this->buildFilterByLinked($condition);

            return $this;
        }

        parent::addFieldToFilter($field, $condition);

        return $this;
    }

    private function buildFilterByLinked($condition): void
    {
        $conditionValue = (int)$condition['eq'];
        $column = \M2E\Otto\Model\ResourceModel\Listing\Other::COLUMN_MAGENTO_PRODUCT_ID;

        if ($conditionValue === \M2E\Otto\Ui\Select\YesNoAnyOption::OPTION_YES) {
            $this->getSelect()->where(sprintf('%s IS NOT NULL', $column));
        } elseif ($conditionValue === \M2E\Otto\Ui\Select\YesNoAnyOption::OPTION_NO) {
            $this->getSelect()->where(sprintf('%s IS NULL', $column));
        }
    }
}
