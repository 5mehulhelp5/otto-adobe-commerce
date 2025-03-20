<?php

namespace M2E\Otto\Helper;

class View
{
    public const LISTING_CREATION_MODE_FULL = 0;
    public const LISTING_CREATION_MODE_LISTING_ONLY = 1;

    public const MOVING_LISTING_OTHER_SELECTED_SESSION_KEY = 'moving_listing_other_selected';
    public const MOVING_LISTING_PRODUCTS_SELECTED_SESSION_KEY = 'moving_listing_products_selected';

    /** @var \Magento\Backend\Model\UrlInterface */
    private $urlBuilder;
    /** @var \M2E\Otto\Helper\View\Otto */
    private $viewHelper;
    /** @var \M2E\Otto\Helper\View\Otto\Controller */
    private $controllerHelper;
    /** @var \Magento\Framework\App\RequestInterface */
    private $request;

    public function __construct(
        \Magento\Backend\Model\UrlInterface $urlBuilder,
        \M2E\Otto\Helper\View\Otto $viewHelper,
        \M2E\Otto\Helper\View\Otto\Controller $controllerHelper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->viewHelper = $viewHelper;
        $this->controllerHelper = $controllerHelper;
        $this->request = $request;
    }

    public function getViewHelper(): View\Otto
    {
        return $this->viewHelper;
    }

    public function getControllerHelper(): View\Otto\Controller
    {
        return $this->controllerHelper;
    }

    public function getCurrentView(): ?string
    {
        $controllerName = $this->request->getControllerName();

        if ($controllerName === null) {
            return null;
        }

        if (stripos($controllerName, \M2E\Otto\Helper\View\Otto::NICK) !== false) {
            return \M2E\Otto\Helper\View\Otto::NICK;
        }

        if (stripos($controllerName, 'system_config') !== false) {
            return \M2E\Otto\Helper\View\Configuration::NICK;
        }

        return null;
    }

    // ---------------------------------------

    public function isCurrentViewOtto(): bool
    {
        return $this->getCurrentView() == \M2E\Otto\Helper\View\Otto::NICK;
    }

    public function isCurrentViewConfiguration(): bool
    {
        return $this->getCurrentView() == \M2E\Otto\Helper\View\Configuration::NICK;
    }

    public function getUrl($row, $controller, $action, array $params = []): string
    {
        return $this->urlBuilder->getUrl("*/otto_$controller/$action", $params);
    }

    public function getModifiedLogMessage($logMessage)
    {
        return \M2E\Core\Helper\Data::escapeHtml(
            \M2E\Otto\Helper\Module\Log::decodeDescription($logMessage),
            ['a'],
            ENT_NOQUOTES
        );
    }
}
