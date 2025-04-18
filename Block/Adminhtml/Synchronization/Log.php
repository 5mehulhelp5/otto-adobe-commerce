<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Synchronization;

use M2E\Otto\Block\Adminhtml\Magento\Grid\AbstractContainer;

class Log extends AbstractContainer
{
    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('synchronizationLog');
        $this->_controller = 'adminhtml_synchronization_log';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $this->_headerText = '';
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        // Set template
        // ---------------------------------------
        $this->setTemplate('M2E_Otto::magento/grid/container/only_content.phtml');
        // ---------------------------------------
    }

    protected function _toHtml()
    {
        $helpBlock = $this
            ->getLayout()
            ->createBlock(
                \M2E\Otto\Block\Adminhtml\HelpBlock::class,
                '',
                [
                    'data' => [
                        'content' => __(
                            'The Log includes information about synchronization of
                             M2E Otto Listings, Orders, Shops, Unmanaged Listings.'
                        ),
                    ],
                ]
            );

        return $helpBlock->toHtml() . parent::_toHtml();
    }
}
