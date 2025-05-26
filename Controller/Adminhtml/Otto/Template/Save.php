<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Template;

use M2E\Otto\Model\Exception\ShippingProfilesUnableProcess;

class Save extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractTemplate
{
    private \M2E\Otto\Helper\Module\Wizard $wizardHelper;
    private \M2E\Core\Helper\Url $urlHelper;
    private \M2E\Otto\Model\Template\Synchronization\SaveService $synchronizationSaveService;
    private \M2E\Otto\Model\Template\Description\SaveService $descriptionSaveService;
    private \M2E\Otto\Model\Template\SellingFormat\SaveService $sellingFormatSaveService;
    private \M2E\Otto\Model\Template\Shipping\SaveService $shippingSaveService;

    public function __construct(
        \M2E\Otto\Model\Template\SellingFormat\SaveService $sellingFormatSaveService,
        \M2E\Otto\Model\Template\Synchronization\SaveService $synchronizationSaveService,
        \M2E\Otto\Model\Template\Description\SaveService $descriptionSaveService,
        \M2E\Otto\Model\Template\Shipping\SaveService $shippingSaveService,
        \M2E\Otto\Helper\Module\Wizard $wizardHelper,
        \M2E\Core\Helper\Url $urlHelper,
        \M2E\Otto\Model\Otto\Template\Manager $templateManager
    ) {
        parent::__construct($templateManager);

        $this->wizardHelper = $wizardHelper;
        $this->urlHelper = $urlHelper;
        $this->synchronizationSaveService = $synchronizationSaveService;
        $this->descriptionSaveService = $descriptionSaveService;
        $this->sellingFormatSaveService = $sellingFormatSaveService;
        $this->shippingSaveService = $shippingSaveService;
    }

    public function execute()
    {
        $templates = [];
        $templateNicks = $this->templateManager->getAllTemplates();

        // ---------------------------------------
        foreach ($templateNicks as $nick) {
            if ($this->isSaveAllowed($nick)) {
                $template = $this->saveTemplate($nick);

                if ($template) {
                    $templates[] = [
                        'nick' => $nick,
                        'id' => (int)$template->getId(),
                        'title' => \M2E\Core\Helper\Data::escapeJs(
                            \M2E\Core\Helper\Data::escapeHtml($template->getTitle())
                        ),
                    ];
                }
            }
        }
        // ---------------------------------------

        // ---------------------------------------
        if ($this->isAjax()) {
            $this->setJsonContent($templates);

            return $this->getResult();
        }
        // ---------------------------------------

        if (count($templates) == 0) {
            $this->messageManager->addError(__('Policy was not saved.'));

            return $this->_redirect('*/*/index');
        }

        $template = array_shift($templates);

        $this->messageManager->addSuccess(__('Policy was saved.'));

        $extendedRoutersParams = [
            'edit' => [
                'id' => $template['id'],
                'nick' => $template['nick'],
                'close_on_save' => $this->getRequest()->getParam('close_on_save'),
            ],
        ];

        if ($this->wizardHelper->isActive(\M2E\Otto\Helper\View\Otto::WIZARD_INSTALLATION_NICK)) {
            $extendedRoutersParams['edit']['wizard'] = true;
        }

        return $this->_redirect(
            $this->urlHelper->getBackUrl(
                'list',
                [],
                $extendedRoutersParams
            )
        );
    }

    protected function isSaveAllowed($templateNick)
    {
        if (!$this->getRequest()->isPost()) {
            return false;
        }

        $requestedTemplateNick = $this->getRequest()->getPost('nick');

        if ($requestedTemplateNick === null) {
            return true;
        }

        if ($requestedTemplateNick == $templateNick) {
            return true;
        }

        return false;
    }

    protected function saveTemplate($nick)
    {
        $data = $this->getRequest()->getPost($nick);

        if ($data === null) {
            return null;
        }

        if ($nick === \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_SYNCHRONIZATION) {
            return $this->synchronizationSaveService->save($data);
        }

        if ($nick === \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_DESCRIPTION) {
            return $this->descriptionSaveService->save($data);
        }

        if ($nick === \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_SELLING_FORMAT) {
            return $this->sellingFormatSaveService->save($data);
        }

        if ($nick === \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_SHIPPING) {
            try {
                return $this->shippingSaveService->save($data);
            } catch (\M2E\Otto\Model\Exception\AccountMissingPermissions $exception) {
                $url = $this->getUrl('*/otto_account/edit', ['id' => $exception->getAccount()->getId()]);
                $this->addExtendedErrorMessage(
                    __(
                        'You are not authorized to access Shipping Profiles. ' .
                        'Please use the "Update Access Data" button in your <a href="%url">%channel_title Seller Account</a> to proceed.',
                        [
                            'url' => $url,
                            'channel_title' => \M2E\Otto\Helper\Module::getChannelTitle()
                        ]
                    )
                );

                return null;
            } catch (ShippingProfilesUnableProcess $exception) {
                foreach ($exception->getErrorMessages() as $error) {
                    $this->getMessageManager()->addErrorMessage($error->getText());
                }

                return null;
            }
        }

        throw new \M2E\Otto\Model\Exception\Logic('Unknown nick ' . $nick);
    }
}
