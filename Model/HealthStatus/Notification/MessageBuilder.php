<?php

declare(strict_types=1);

namespace M2E\Otto\Model\HealthStatus\Notification;

class MessageBuilder
{
    private \Magento\Backend\Model\UrlInterface $urlBuilder;

    public function __construct(
        \Magento\Backend\Model\UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return string
     */
    public function build(): string
    {
        return $this->getHeader() . ': ' . $this->getMessage();
    }

    /**
     * @return string
     */
    public function getHeader(): string
    {
        return (string)__('M2E Otto Health Status Notification');
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return (string)__(
            'Something went wrong with your M2E Otto running and some actions from your side are required. ' .
            'You can find detailed information in <a target="_blank" href="%url">M2E Otto Health Status Center</a>.',
            ['url' => $this->urlBuilder->getUrl('m2e_otto/healthStatus/index')]
        );
    }
}
