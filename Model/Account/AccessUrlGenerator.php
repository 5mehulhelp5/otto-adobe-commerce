<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Account;

class AccessUrlGenerator
{
    private const ROUTE_AFTER_INSTALL_GENERAL = 'm2e_otto/otto_account/beforeGetToken';
    private const ROUTE_AFTER_GET_AUTH_GENERAL = 'm2e_otto/otto_account/afterGetToken';

    private const ROUTE_AFTER_INSTALL_WIZARD = 'm2e_otto/wizard_installationOtto/beforeGetToken';
    private const ROUTE_AFTER_GET_AUTH_WIZARD = 'm2e_otto/wizard_installationOtto/afterGetToken';

    private \Magento\Backend\Model\UrlInterface $url;
    private \M2E\Otto\Model\Otto\Connector\Account\GetInstallUrl\Processor $getInstallUrlProcessor;
    private \M2E\Otto\Model\Otto\Connector\Account\GetGrantAccessUrl\Processor $getGrantAccessUrlProcessor;

    public function __construct(
        \Magento\Backend\Model\UrlInterface $url,
        \M2E\Otto\Model\Otto\Connector\Account\GetInstallUrl\Processor $getInstallUrlProcessor,
        \M2E\Otto\Model\Otto\Connector\Account\GetGrantAccessUrl\Processor $getGrantAccessUrlProcessor
    ) {
        $this->url = $url;
        $this->getInstallUrlProcessor = $getInstallUrlProcessor;
        $this->getGrantAccessUrlProcessor = $getGrantAccessUrlProcessor;
    }

    public function getInstallUrlForCreate(string $title, string $mode): string
    {
        $backUrl = $this->url->getUrl(
            self::ROUTE_AFTER_INSTALL_GENERAL,
            ['title' => $title, 'mode' => $mode]
        );

        return $this->retrieveInstallUrl($backUrl, $mode);
    }

    public function getInstallUrlForCreateFromWizard(string $title, string $mode): string
    {
        $backUrl = $this->url->getUrl(
            self::ROUTE_AFTER_INSTALL_WIZARD,
            ['title' => $title, 'mode' => $mode]
        );

        return $this->retrieveInstallUrl($backUrl, $mode);
    }

    public function getInstallUrlForUpdate(int $accountId, string $mode): string
    {
        $backUrl = $this->url->getUrl(
            self::ROUTE_AFTER_INSTALL_GENERAL,
            ['account_id' => $accountId,  'mode' => $mode]
        );

        return $this->retrieveInstallUrl($backUrl, $mode);
    }

    public function createParamAfterInstall(\Magento\Framework\App\RequestInterface $request): AccessParams\Install
    {
        $title = $request->getParam('title');
        $accountId = (int)$request->getParam('account_id');

        return new AccessParams\Install(
            $accountId === 0 ? null : $accountId,
            empty($title) ? null : $title
        );
    }

    // ----------------------------------------

    public function getGrantAccessUrlForCreate(string $title, string $mode): string
    {
        $backUrl = $this->url->getUrl(
            self::ROUTE_AFTER_GET_AUTH_GENERAL,
            ['title' => $title, 'mode' => $mode]
        );

        return $this->retrieveAuthUrl($backUrl, $mode);
    }

    public function getGrantAccessUrlForCreateFromWizard(string $title, string $mode): string
    {
        $backUrl = $this->url->getUrl(
            self::ROUTE_AFTER_GET_AUTH_WIZARD,
            ['title' => $title, 'mode' => $mode]
        );

        return $this->retrieveAuthUrl($backUrl, $mode);
    }

    public function getGrantAccessUrlForUpdate(int $accountId, string $mode): string
    {
        $backUrl = $this->url->getUrl(
            self::ROUTE_AFTER_GET_AUTH_GENERAL,
            ['account_id' => $accountId, 'mode' => $mode]
        );

        return $this->retrieveAuthUrl($backUrl, $mode);
    }

    public function createParamAfterGrantAccess(
        \Magento\Framework\App\RequestInterface $request
    ): AccessParams\GrantAccess {
        $title = $request->getParam('title');
        $accountId = (int)$request->getParam('account_id');
        $authCode = $request->getParam('code');
        $mode = $request->getParam('mode');

        return new AccessParams\GrantAccess(
            (string)$authCode,
            $accountId === 0 ? null : $accountId,
            empty($title) ? null : $title,
            empty($mode) ? \M2E\Otto\Model\Account::MODE_PRODUCTION : $mode,
        );
    }

    // ----------------------------------------

    private function retrieveInstallUrl(string $backUrl, $mode): string
    {
        return $this->getInstallUrlProcessor
            ->process($backUrl, $mode)
            ->getUrl();
    }

    private function retrieveAuthUrl(string $backUrl, string $mode): string
    {
        return $this->getGrantAccessUrlProcessor
            ->process($backUrl, $mode)
            ->getUrl();
    }
}
