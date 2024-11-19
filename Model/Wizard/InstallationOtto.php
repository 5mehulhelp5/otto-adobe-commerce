<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Wizard;

class InstallationOtto extends \M2E\Otto\Model\Wizard
{
    /** @var string[] */
    protected $steps = [
        'registration',
        'account',
        'settings',

        'listingTutorial',
        'listingGeneral',
        'listingTemplates',
    ];

    /**
     * @return string
     */
    public function getNick()
    {
        return \M2E\Otto\Helper\View\Otto::WIZARD_INSTALLATION_NICK;
    }
}
