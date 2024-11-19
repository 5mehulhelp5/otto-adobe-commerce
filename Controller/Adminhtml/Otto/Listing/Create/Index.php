<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Listing\Create;

use M2E\Otto\Model\Listing;

class Index extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractListing
{
    private \M2E\Otto\Model\Otto\Listing\Transferring $transferring;
    private \M2E\Otto\Model\Listing\LogService $listingLogService;
    private \M2E\Otto\Helper\Data\Session $sessionHelper;
    private \M2E\Otto\Model\ListingFactory $listingFactory;
    private Listing\Repository $listingRepository;
    private \M2E\Otto\Helper\Module\Wizard $wizardHelper;
    private \M2E\Otto\Model\Listing\Wizard\Create $createModel;

    public function __construct(
        \M2E\Otto\Model\Listing\Repository $listingRepository,
        \M2E\Otto\Model\ListingFactory $listingFactory,
        \M2E\Otto\Helper\Data\Session $sessionHelper,
        \M2E\Otto\Model\Listing\LogService $listingLogService,
        \M2E\Otto\Model\Otto\Listing\Transferring $transferring,
        \M2E\Otto\Helper\Module\Wizard $wizardHelper,
        \M2E\Otto\Model\Listing\Wizard\Create $createModel
    ) {
        parent::__construct();
        $this->createModel = $createModel;
        $this->transferring = $transferring;
        $this->listingLogService = $listingLogService;
        $this->sessionHelper = $sessionHelper;
        $this->listingFactory = $listingFactory;
        $this->listingRepository = $listingRepository;
        $this->wizardHelper = $wizardHelper;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_Otto::listings_items');
    }

    public function execute()
    {
        $step = (int)$this->getRequest()->getParam('step');

        switch ($step) {
            case 1:
                $this->stepOne();
                break;
            case 2:
                $this->stepTwo();
                if ($this->getRequest()->isPost() && $this->isCreationModeListingOnly()) {
                    // closing window for Unmanaged products moving in new listing creation

                    return $this->getRawResult();
                }
                break;
            default:
                $this->clearSession();
                $this->_redirect('*/*/index', ['_current' => true, 'step' => 1]);
                break;
        }

        $this->getResultPage()->getConfig()->getTitle()->prepend(__('New Listing Creation'));
        $this->setPageHelpLink('https://docs-m2.m2epro.com/create-m2e-otto-listing');

        return $this->getResult();
    }

    private function stepOne()
    {
        if ($this->getRequest()->getParam('clear')) {
            $this->clearSession();
            $this->getRequest()->setParam('clear', null);
            $this->_redirect('*/*/index', ['_current' => true, 'step' => 1]);

            return;
        }

        $this->setWizardStep('listingGeneral');

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            $this->setSessionValue('title', strip_tags($post['title']));
            $this->setSessionValue('account_id', (int)$post['account_id']);
            $this->setSessionValue('store_id', (int)$post['store_id']);

            $this->_redirect('*/*/index', ['_current' => true, 'step' => 2]);

            return;
        }

        $listingOnlyMode = \M2E\Otto\Helper\View::LISTING_CREATION_MODE_LISTING_ONLY;
        if ($this->getRequest()->getParam('creation_mode') == $listingOnlyMode) {
            $this->setSessionValue('creation_mode', $listingOnlyMode);
        }

        $this->addContent(
            $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Otto\Listing\Create\General::class)
        );
    }

    private function stepTwo()
    {
        if (
            $this->getSessionValue('account_id') === null
        ) {
            $this->clearSession();
            $this->_redirect('*/*/index', ['_current' => true, 'step' => 1]);

            return;
        }

        if ($this->getRequest()->isPost()) {
            $form = $this->getLayout()->createBlock(
                \M2E\Otto\Block\Adminhtml\Otto\Listing\Create\Templates\Form::class
            );
            $dataKeys = $form->getDefaultFieldsValues();

            $post = $this->getRequest()->getPost();
            foreach ($dataKeys as $key => $value) {
                $this->setSessionValue($key, $post[$key]);
            }

            $listing = $this->createListing();

            if ($listingId = $this->getRequest()->getParam('listing_id')) {
                $this->transferring->setListing(
                    $this->listingRepository->get($listingId)
                );

                $this->clearSession();
                $this->transferring->setTargetListingId($listing->getId());

                $this->_redirect(
                    '*/otto_listing/transferring/index',
                    [
                        'listing_id' => $listingId,
                        'step' => 3,
                    ]
                );

                return;
            }

            if ($this->isCreationModeListingOnly()) {
                // closing window for Unmanaged products moving/from another listing in new listing creation
                $this->getRawResult()->setContents("<script>window.close();</script>");

                return;
            }

            $wizard = $this->createModel->process($listing, \M2E\Otto\Model\Listing\Wizard::TYPE_GENERAL);

            $this->_redirect(
                '*/listing_wizard/index',
                [
                    'id' => $wizard->getId()
                ]
            );

            return;
        }

        $this->setWizardStep('listingTemplates');
        $this->setWizardStatusCompleted();

        $this->addContent(
            $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Otto\Listing\Create\Templates::class)
        );
    }

    private function createListing()
    {
        $data = $this->getSessionValue();
        $model = $this->listingFactory->create();
        $model->addData($data);
        $model->save();

        $this->listingLogService->addListing(
            $model,
            \M2E\Otto\Helper\Data::INITIATOR_USER,
            \M2E\Otto\Model\Listing\Log::ACTION_ADD_LISTING,
            null,
            (string)__('Listing was Added'),
            \M2E\Otto\Model\Log\AbstractModel::TYPE_INFO
        );

        return $model;
    }

    protected function setSessionValue($key, $value): self
    {
        $sessionData = $this->getSessionValue();
        $sessionData[$key] = $value;

        $this->sessionHelper->setValue(Listing::CREATE_LISTING_SESSION_DATA, $sessionData);

        return $this;
    }

    protected function getSessionValue($key = null)
    {
        $sessionData = $this->sessionHelper->getValue(Listing::CREATE_LISTING_SESSION_DATA);
        if ($sessionData === null) {
            $sessionData = [];
        }

        if ($key === null) {
            return $sessionData;
        }

        return $sessionData[$key] ?? null;
    }

    private function clearSession()
    {
        $this->sessionHelper->setValue(Listing::CREATE_LISTING_SESSION_DATA, null);
    }

    private function setWizardStep($step)
    {
        if (!$this->wizardHelper->isActive(\M2E\Otto\Helper\View\Otto::WIZARD_INSTALLATION_NICK)) {
            return;
        }

        $this->wizardHelper->setStep(\M2E\Otto\Helper\View\Otto::WIZARD_INSTALLATION_NICK, $step);
    }

    private function setWizardStatusCompleted()
    {
        if (!$this->wizardHelper->isActive(\M2E\Otto\Helper\View\Otto::WIZARD_INSTALLATION_NICK)) {
            return;
        }

        $this->wizardHelper->setStatus(
            \M2E\Otto\Helper\View\Otto::WIZARD_INSTALLATION_NICK,
            \M2E\Otto\Helper\Module\Wizard::STATUS_COMPLETED
        );
    }

    private function isCreationModeListingOnly()
    {
        return $this->getSessionValue(
            'creation_mode'
        ) === \M2E\Otto\Helper\View::LISTING_CREATION_MODE_LISTING_ONLY;
    }
}
