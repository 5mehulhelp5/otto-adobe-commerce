<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Connector;

class RequestBuilder
{
    private const API_VERSION = 1;

    private \M2E\Otto\Helper\Magento $magentoHelper;
    private \M2E\Otto\Helper\Client $clientHelper;
    private \M2E\Otto\Model\Connector\Client\Config $config;
    private \M2E\Otto\Model\Module $module;

    public function __construct(
        \M2E\Otto\Model\Connector\Client\Config $config,
        \M2E\Otto\Helper\Magento $magentoHelper,
        \M2E\Otto\Helper\Client $clientHelper,
        \M2E\Otto\Model\Module $module
    ) {
        $this->module = $module;
        $this->magentoHelper = $magentoHelper;
        $this->clientHelper = $clientHelper;
        $this->config = $config;
    }

    public function build(
        \M2E\Otto\Model\Connector\CommandInterface $command,
        \M2E\Otto\Model\Connector\ProtocolInterface $protocol
    ): array {
        $request = new \M2E\Otto\Model\Connector\Request();

        $request->setComponent($protocol->getComponent())
                ->setComponentVersion($protocol->getComponentVersion())
                ->setCommand($command->getCommand())
                ->setInput($command->getRequestData())
                ->setPlatform(
                    sprintf('%s (%s)', $this->magentoHelper->getName(), $this->magentoHelper->getEditionName()),
                    $this->magentoHelper->getVersion(false),
                )
                ->setModule($this->module->getName(), $this->module->getPublicVersion())
                ->setLocation($this->clientHelper->getDomain(), $this->clientHelper->getIp())
                ->setAuth(
                    $this->config->getApplicationKey(),
                    $this->config->getLicenseKey(),
                );

        return [
            'api_version' => self::API_VERSION,
            'request' => \M2E\Otto\Helper\Json::encode($request->getInfo()),
            'data' => \M2E\Otto\Helper\Json::encode($request->getInput()),
        ];
    }
}
