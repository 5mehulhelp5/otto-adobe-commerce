<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Account;

class HelpBlock extends \M2E\Otto\Block\Adminhtml\HelpBlock
{
    public function getContent(): string
    {
        return (string)__(
            '<p>On this Page you can find information about Otto Accounts which can be managed via M2E Otto.</p><br>
                <p>Settings for such configurations as Otto Orders along with Magento Order creation conditions,
                Unmanaged Listings import including options of Linking them to Magento Products and Moving them
                to M2E Otto Listings,
                etc. can be specified for each Account separately.</p><br>
                <p><strong>Note:</strong> Otto Account can be deleted only if it is not being used for any of M2E Otto Listings.</p>'
        );
    }
}
