<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Template\ChangeProcessor;

abstract class ChangeProcessorAbstract extends \M2E\Otto\Model\Template\ChangeProcessorAbstract
{
    public const INSTRUCTION_TYPE_QTY_DATA_CHANGED = 'template_qty_data_changed';
    public const INSTRUCTION_TYPE_PRICE_DATA_CHANGED = 'template_price_data_changed';
    public const INSTRUCTION_TYPE_TITLE_DATA_CHANGED = 'template_title_data_changed';
    public const INSTRUCTION_TYPE_DESCRIPTION_DATA_CHANGED = 'template_description_data_changed';
    public const INSTRUCTION_TYPE_IMAGES_DATA_CHANGED = 'template_images_data_changed';
    public const INSTRUCTION_TYPE_CATEGORIES_DATA_CHANGED = 'template_categories_data_changed';
    public const INSTRUCTION_TYPE_OTHER_DATA_CHANGED = 'template_other_data_changed';
    public const INSTRUCTION_TYPE_SHIPPING_DATA_CHANGED = 'template_shipping_data_changed';
}
