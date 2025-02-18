<?php

namespace M2E\Otto\Controller\Adminhtml;

use M2E\Otto\Model\ActiveRecord\Factory as ActiveRecordFactory;
use Magento\Backend\App\Action;

abstract class AbstractBase extends Action
{
    public const LAYOUT_ONE_COLUMN = '1column';
    public const LAYOUT_TWO_COLUMNS = '2columns';
    public const LAYOUT_BLANK = 'blank';

    public const MESSAGE_IDENTIFIER = 'm2e_otto_messages';
    public const GLOBAL_MESSAGES_GROUP = 'm2e_otto_global_messages_group';

    /** @var ActiveRecordFactory $activeRecordFactory */
    protected $activeRecordFactory;
    /** @var \Magento\Framework\View\Result\PageFactory $resultPageFactory */
    protected $resultPageFactory;
    /** @var \Magento\Framework\Controller\Result\RawFactory $resultRawFactory */
    protected $resultRawFactory;
    /** @var \Magento\Framework\View\LayoutFactory $layoutFactory */
    protected $layoutFactory;
    /** @var \M2E\Otto\Block\Adminhtml\Magento\Renderer\CssRenderer $cssRenderer */
    protected $cssRenderer;
    /** @var \Magento\Framework\App\ResourceConnection */
    protected $resourceConnection;
    /** @var \Magento\Config\Model\Config */
    protected $magentoConfig;
    /** @var \Magento\Framework\Controller\Result\Raw $rawResult */
    protected $rawResult;
    /** @var \Magento\Framework\View\LayoutInterface $emptyLayout */
    protected $emptyLayout;
    /** @var \Magento\Framework\View\Result\Page $resultPage */
    protected $resultPage;
    /** @var \Magento\Framework\App\Response\RedirectInterface */
    protected $redirect;

    /** @var bool */
    private $generalBlockWasAppended = false;

    public function __construct($context = null)
    {
        /** @var \M2E\Otto\Controller\Adminhtml\Context $context */
        $context = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\Otto\Controller\Adminhtml\Context::class
        );

        $this->activeRecordFactory = $context->getActiveRecordFactory();
        $this->resultPageFactory = $context->getResultPageFactory();
        $this->resultRawFactory = $context->getResultRawFactory();
        $this->layoutFactory = $context->getLayoutFactory();
        $this->cssRenderer = $context->getCssRenderer();
        $this->resourceConnection = $context->getResourceConnection();
        $this->magentoConfig = $context->getMagentoConfig();
        $this->redirect = $context->getRedirect();

        parent::__construct($context);
    }

    //########################################

    protected function _isAllowed()
    {
        return $this->_auth->isLoggedIn();
    }

    //########################################

    protected function isAjax(\Magento\Framework\App\RequestInterface $request = null)
    {
        if ($request === null) {
            $request = $this->getRequest();
        }

        return $request->isXmlHttpRequest() || $request->getParam('isAjax');
    }

    //########################################

    protected function getLayoutType()
    {
        return self::LAYOUT_ONE_COLUMN;
    }

    //########################################

    public function getMessageManager()
    {
        return $this->messageManager;
    }

    // ---------------------------------------

    protected function addExtendedErrorMessage($message, $group = null)
    {
        $this->getMessageManager()->addComplexErrorMessage(
            self::MESSAGE_IDENTIFIER,
            ['content' => (string)$message],
            $group
        );
    }

    protected function addExtendedWarningMessage($message, $group = null)
    {
        $this->getMessageManager()->addComplexWarningMessage(
            self::MESSAGE_IDENTIFIER,
            ['content' => (string)$message],
            $group
        );
    }

    protected function addExtendedNoticeMessage($message, $group = null)
    {
        $this->getMessageManager()->addComplexNoticeMessage(
            self::MESSAGE_IDENTIFIER,
            ['content' => (string)$message],
            $group
        );
    }

    protected function addExtendedSuccessMessage($message, $group = null)
    {
        $this->getMessageManager()->addComplexSuccessMessage(
            self::MESSAGE_IDENTIFIER,
            ['content' => (string)$message],
            $group
        );
    }

    //########################################

    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        if (($preDispatchResult = $this->preDispatch($request)) !== true) {
            return $preDispatchResult;
        }

        /** @var \M2E\Otto\Helper\Module\Exception $exceptionHelper */
        $exceptionHelper = $this->_objectManager->get(\M2E\Otto\Helper\Module\Exception::class);

        $exceptionHelper->setFatalErrorHandler();

        try {
            $result = parent::dispatch($request);
        } catch (\Throwable $exception) {
            if ($request->getControllerName() === \M2E\Otto\Helper\Module\Support::SUPPORT_CONTROLLER_NAME) {
                $this->getRawResult()->setContents($exception->getMessage());

                return $this->getRawResult();
            }

            /** @var \M2E\Otto\Helper\Module $moduleHelper */
            $moduleHelper = $this->_objectManager->get(\M2E\Otto\Helper\Module::class);
            if ($moduleHelper->isDevelopmentEnvironment()) {
                throw $exception;
            }

            $exceptionHelper->process($exception);

            if ($request->isXmlHttpRequest() || $request->getParam('isAjax')) {
                $this->getRawResult()->setContents($exception->getMessage());

                return $this->getRawResult();
            }

            $this->getMessageManager()->addError(
                $exceptionHelper->getUserMessage($exception)
            );

            $params = [
                'error' => 'true',
            ];

            return $this->_redirect('*/support/index', $params);
        }

        $this->postDispatch($request);

        return $result;
    }

    // ---------------------------------------

    protected function preDispatch(\Magento\Framework\App\RequestInterface $request)
    {
        /** @var \M2E\Otto\Helper\Module\Maintenance $maintenanceModule */
        $maintenanceModule = $this->_objectManager->get(\M2E\Otto\Helper\Module\Maintenance::class);
        if ($maintenanceModule->isEnabled()) {
            return $this->_redirect('*/maintenance');
        }

        /** @var \M2E\Otto\Helper\Module $moduleHelper */
        $moduleHelper = $this->_objectManager->get(\M2E\Otto\Helper\Module::class);

        if ($moduleHelper->isDisabled()) {
            $message = __(
                'M2E Otto is disabled. Inventory and Order synchronization is not running. ' .
                'The Module interface is unavailable.<br>' .
                'You can enable the Module under <i>Stores > Settings > Configuration > M2E Otto > Module</i>.'
            );
            $this->getMessageManager()->addNotice($message);

            return $this->_redirect('admin/dashboard');
        }

        if ($this->isAjax($request) && !$this->_auth->isLoggedIn()) {
            $this->getRawResult()->setContents(
                \M2E\Otto\Helper\Json::encode([
                    'ajaxExpired' => 1,
                    'ajaxRedirect' => $this->redirect->getRefererUrl(),
                ])
            );

            return $this->getRawResult();
        }

        return true;
    }

    protected function postDispatch(\Magento\Framework\App\RequestInterface $request)
    {
        ob_get_clean();

        if ($this->isAjax($request)) {
            return;
        }

        if ($this->getLayoutType() === self::LAYOUT_BLANK) {
            $this->addCss('layout/blank.css');
        }

        foreach ($this->cssRenderer->getFiles() as $file) {
            $this->addCss($file);
        }
    }

    //########################################

    protected function getLayout()
    {
        if ($this->isAjax()) {
            $this->initEmptyLayout();

            return $this->emptyLayout;
        }

        return $this->getResultPage()->getLayout();
    }

    protected function initEmptyLayout()
    {
        if ($this->emptyLayout !== null) {
            return;
        }

        $this->emptyLayout = $this->layoutFactory->create();
    }

    // ---------------------------------------

    protected function getResult()
    {
        if ($this->isAjax()) {
            return $this->getRawResult();
        }

        return $this->getResultPage();
    }

    // ---------------------------------------

    protected function getResultPage()
    {
        if ($this->resultPage === null) {
            $this->initResultPage();
        }

        return $this->resultPage;
    }

    protected function initResultPage()
    {
        if ($this->resultPage !== null) {
            return;
        }

        $this->resultPage = $this->resultPageFactory->create();
        $this->resultPage->addHandle($this->getLayoutType());

        $this->resultPage->getConfig()->getTitle()->set(__('Otto'));
    }

    // ---------------------------------------

    protected function getRawResult()
    {
        if ($this->rawResult === null) {
            $this->initRawResult();
        }

        return $this->rawResult;
    }

    protected function initRawResult()
    {
        if ($this->rawResult !== null) {
            return;
        }

        $this->rawResult = $this->resultRawFactory->create();
    }

    //########################################

    protected function addLeft(\Magento\Framework\View\Element\AbstractBlock $block)
    {
        if ($this->getLayoutType() != self::LAYOUT_TWO_COLUMNS) {
            throw new \M2E\Otto\Model\Exception\Logic('Add left can not be used for non two column layout');
        }

        $this->initResultPage();
        $this->appendGeneralBlock();

        return $this->_addLeft($block);
    }

    protected function addContent(\Magento\Framework\View\Element\AbstractBlock $block)
    {
        $this->initResultPage();
        $this->beforeAddContentEvent();
        $this->appendGeneralBlock();

        return $this->_addContent($block);
    }

    protected function setRawContent($content)
    {
        return $this->getRawResult()->setContents($content);
    }

    protected function setAjaxContent($blockData, $appendGeneralBlock = true)
    {
        if ($blockData instanceof \Magento\Framework\View\Element\AbstractBlock) {
            $blockData = $blockData->toHtml();
        }

        if (!$this->generalBlockWasAppended && $appendGeneralBlock) {
            $generalBlock = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\General::class);
            $generalBlock->setIsAjax(true);
            $blockData = $generalBlock->toHtml() . $blockData;
            $this->generalBlockWasAppended = true;
        }

        $this->getRawResult()->setContents($blockData);
    }

    /**
     * If key 'html' is exists, general block will be appended
     *
     * @param array $data
     */
    protected function setJsonContent(array $data)
    {
        if (!$this->generalBlockWasAppended && isset($data['html'])) {
            $generalBlock = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\General::class);
            $generalBlock->setIsAjax(true);
            $data['html'] = $generalBlock->toHtml() . $data['html'];
            $this->generalBlockWasAppended = true;
        }

        $this->setAjaxContent(\M2E\Otto\Helper\Json::encode($data), false);
    }

    // ---------------------------------------

    protected function addCss($file)
    {
        $this->getResultPage()->getConfig()->addPageAsset("M2E_Otto::css/$file");
    }

    // ---------------------------------------

    protected function beforeAddContentEvent()
    {
        return null;
    }

    //########################################

    protected function appendGeneralBlock()
    {
        if ($this->generalBlockWasAppended) {
            return;
        }

        $generalBlock = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\General::class);
        $this->getLayout()->setChild('js', $generalBlock->getNameInLayout(), '');

        $this->generalBlockWasAppended = true;
    }

    //########################################

    protected function getRequestIds($key = 'id')
    {
        $id = $this->getRequest()->getParam($key);
        $ids = $this->getRequest()->getParam($key . 's');

        if ($id === null && $ids === null) {
            return [];
        }

        $requestIds = [];

        if ($ids !== null) {
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }
            $requestIds = (array)$ids;
        }

        if ($id !== null) {
            $requestIds[] = $id;
        }

        return array_filter($requestIds);
    }

    //########################################

    protected function setPageHelpLink($link)
    {
        /** @var \Magento\Theme\Block\Html\Title $pageTitleBlock */
        $pageTitleBlock = $this->getLayout()->getBlock('page.title');

        $helpLinkBlock = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\PageHelpLink::class)->setData([
            'page_help_link' => $link,
        ]);

        $pageTitleBlock->setTitleClass('Otto-page-title');
        $pageTitleBlock->setChild('Otto.page.help.block', $helpLinkBlock);
    }

    //########################################

    /**
     * Clears global messages session to prevent duplicate
     * @inheritdoc
     */
    protected function _redirect($path, $arguments = [])
    {
        $this->messageManager->getMessages(true, self::GLOBAL_MESSAGES_GROUP);

        return parent::_redirect($path, $arguments);
    }

    protected function getViewHelper(): \M2E\Otto\Helper\View
    {
        return $this->_objectManager->get(\M2E\Otto\Helper\View::class);
    }

    /**
     * @return \Magento\Framework\App\Request\Http
     */
    public function getRequest() // @codingStandardsIgnoreLine
    {
        return parent::getRequest();
    }
}
