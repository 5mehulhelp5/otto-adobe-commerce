<?php

namespace M2E\Otto\Model\Log;

abstract class AbstractModel extends \M2E\Otto\Model\ActiveRecord\AbstractModel
{
    /**
     * The order of the values of log types' constants is important.
     * @see \M2E\Otto\Block\Adminhtml\Log\Grid\LastActions::$actionsSortOrder
     * @see \M2E\Otto\Block\Adminhtml\Log\Listing\Product\View\Grouped\AbstractGrid::_prepareCollection()
     */
    public const TYPE_INFO = 1;
    public const TYPE_SUCCESS = 2;
    public const TYPE_WARNING = 3;
    public const TYPE_ERROR = 4;

    protected function validateType(int $type): void
    {
        if (!in_array($type, [self::TYPE_INFO, self::TYPE_SUCCESS, self::TYPE_WARNING, self::TYPE_ERROR], true)) {
            throw new \M2E\Otto\Model\Exception\Logic("Type '$type' is not valid.");
        }
    }
}
