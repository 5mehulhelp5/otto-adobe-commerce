<?php

declare(strict_types=1);

namespace M2E\Otto\Helper\View;

class Otto
{
    public const NICK = 'otto';

    public const WIZARD_INSTALLATION_NICK = 'installationOtto';
    public const MENU_ROOT_NODE_NICK = 'M2E_Otto::otto';
    private \M2E\Otto\Helper\Module\Wizard $wizard;

    public function __construct(
        \M2E\Otto\Helper\Module\Wizard $wizard
    ) {
        $this->wizard = $wizard;
    }

    /**
     * @return string
     */
    public static function getWizardInstallationNick(): string
    {
        return self::WIZARD_INSTALLATION_NICK;
    }

    /**
     * @return bool
     */
    public function isInstallationWizardFinished(): bool
    {
        return $this->wizard->isFinished(
            self::getWizardInstallationNick()
        );
    }
}
