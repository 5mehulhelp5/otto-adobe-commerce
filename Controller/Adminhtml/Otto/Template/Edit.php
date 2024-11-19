<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Template;

use M2E\Otto\Controller\Adminhtml\Otto\AbstractTemplate;

class Edit extends AbstractTemplate
{
    private \M2E\Otto\Helper\Component\Otto\Template\Switcher\DataLoader $dataLoader;
    private \M2E\Otto\Model\Template\Synchronization\Repository $synchronizationRepository;
    private \M2E\Otto\Model\Template\SynchronizationFactory $synchronizationFactory;
    private \M2E\Otto\Model\Template\SellingFormatFactory $sellingFormatFactory;
    private \M2E\Otto\Model\Template\SellingFormat\Repository $sellingFormatRepository;
    private \M2E\Otto\Model\Template\DescriptionFactory $descriptionFactory;
    private \M2E\Otto\Model\Template\Description\Repository $descriptionRepository;
    private \M2E\Otto\Model\Template\Shipping\Repository $shippingRepository;
    private \M2E\Otto\Model\Template\ShippingFactory $shippingFactory;

    public function __construct(
        \M2E\Otto\Model\Template\SellingFormatFactory $sellingFormatFactory,
        \M2E\Otto\Model\Template\SellingFormat\Repository $sellingFormatRepository,
        \M2E\Otto\Model\Template\DescriptionFactory $descriptionFactory,
        \M2E\Otto\Model\Template\Description\Repository $descriptionRepository,
        \M2E\Otto\Model\Template\Synchronization\Repository $synchronizationRepository,
        \M2E\Otto\Model\Template\SynchronizationFactory $synchronizationFactory,
        \M2E\Otto\Model\Template\Shipping\Repository $shippingRepository,
        \M2E\Otto\Model\Template\ShippingFactory $shippingFactory,
        \M2E\Otto\Helper\Component\Otto\Template\Switcher\DataLoader $dataLoader,
        \M2E\Otto\Model\Otto\Template\Manager $templateManager
    ) {
        parent::__construct($templateManager);

        $this->dataLoader = $dataLoader;
        $this->synchronizationRepository = $synchronizationRepository;
        $this->synchronizationFactory = $synchronizationFactory;
        $this->sellingFormatFactory = $sellingFormatFactory;
        $this->sellingFormatRepository = $sellingFormatRepository;
        $this->descriptionRepository = $descriptionRepository;
        $this->descriptionFactory = $descriptionFactory;
        $this->shippingRepository = $shippingRepository;
        $this->shippingFactory = $shippingFactory;
    }

    public function execute()
    {
        // ---------------------------------------
        $id = $this->getRequest()->getParam('id');
        $nick = $this->getRequest()->getParam('nick');
        // ---------------------------------------

        if ($nick === \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_SYNCHRONIZATION) {
            return $this->executeSynchronizationTemplate($id);
        }

        if ($nick === \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_SELLING_FORMAT) {
            return $this->executeSellingFormatTemplate($id);
        }

        if ($nick === \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_DESCRIPTION) {
            return $this->executeDescriptionTemplate($id);
        }

        if ($nick === \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_SHIPPING) {
            return $this->executeShippingTemplate($id);
        }

        throw new \M2E\Otto\Model\Exception\Logic('Unknown nick ' . $nick);
    }

    private function executeSynchronizationTemplate($id)
    {
        $template = $this->synchronizationRepository->find((int)$id);
        if ($template === null) {
            $template = $this->synchronizationFactory->create();
        }

        if (!$template->getId() && $id) {
            $this->getMessageManager()->addError(__('Policy does not exist.'));

            return $this->_redirect('*/*/index');
        }

        $dataLoader = $this->dataLoader;
        $dataLoader->load($template);

        // ---------------------------------------

        $this->setPageHelpLink('https://docs-m2.m2epro.com/synchronization-policy-for-otto');

        if ($template->getId()) {
            $headerText =
                __(
                    'Edit "%template_title" Synchronization Policy',
                    [
                        'template_title' => \M2E\Otto\Helper\Data::escapeHtml($template->getTitle()),
                    ],
                );
        } else {
            $headerText = __('Add Synchronization Policy');
        }

        $content = $this->getLayout()->createBlock(
            \M2E\Otto\Block\Adminhtml\Otto\Template\Edit::class,
            '',
            [
                'data' => [
                    'template_nick' => \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_SYNCHRONIZATION,
                ],
            ]
        );

        $content->toHtml();

        $this->getResult()->getConfig()->getTitle()->prepend($headerText);
        $this->addContent($content);

        return $this->getResult();
    }

    private function executeSellingFormatTemplate($id)
    {
        $template = $this->sellingFormatRepository->find((int)$id);
        if ($template === null) {
            $template = $this->sellingFormatFactory->create();
        }

        if (!$template->getId() && $id) {
            $this->getMessageManager()->addError(__('Policy does not exist.'));

            return $this->_redirect('*/*/index');
        }

        $dataLoader = $this->dataLoader;
        $dataLoader->load($template);

        // ---------------------------------------

        $this->setPageHelpLink('https://docs-m2.m2epro.com/selling-policy-for-otto');

        if ($template->getId()) {
            $headerText =
                __(
                    'Edit "%template_title" Selling Policy',
                    [
                        'template_title' => \M2E\Otto\Helper\Data::escapeHtml($template->getTitle()),
                    ],
                );
        } else {
            $headerText = __('Add Selling Policy');
        }

        $content = $this->getLayout()->createBlock(
            \M2E\Otto\Block\Adminhtml\Otto\Template\Edit::class,
            '',
            [
                'data' => [
                    'template_nick' => \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_SELLING_FORMAT,
                ],
            ]
        );

        $content->toHtml();

        $this->getResult()->getConfig()->getTitle()->prepend($headerText);
        $this->addContent($content);

        return $this->getResult();
    }

    private function executeDescriptionTemplate($id)
    {
        $template = $this->descriptionRepository->find((int)$id);
        if ($template === null) {
            $template = $this->descriptionFactory->create();
        }

        if (!$template->getId() && $id) {
            $this->getMessageManager()->addError(__('Policy does not exist.'));

            return $this->_redirect('*/*/index');
        }

        $dataLoader = $this->dataLoader;
        $dataLoader->load($template);

        $this->setPageHelpLink('https://docs-m2.m2epro.com/description-policy-for-otto');

        if ($template->getId()) {
            $headerText =
                __(
                    'Edit "%template_title" Description Policy',
                    [
                        'template_title' => \M2E\Otto\Helper\Data::escapeHtml($template->getTitle()),
                    ],
                );
        } else {
            $headerText = __('Add Description Policy');
        }

        $content = $this->getLayout()->createBlock(
            \M2E\Otto\Block\Adminhtml\Otto\Template\Edit::class,
            '',
            [
                'data' => [
                    'template_nick' => \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_DESCRIPTION,
                ],
            ]
        );

        $content->toHtml();

        $this->getResult()->getConfig()->getTitle()->prepend($headerText);
        $this->addContent($content);

        return $this->getResult();
    }

    private function executeShippingTemplate($id)
    {
        $template = $this->shippingRepository->find((int)$id);
        if ($template === null) {
            $template = $this->shippingFactory->createEmpty();
        }

        if (!$template->getId() && $id) {
            $this->getMessageManager()->addError(__('Policy does not exist.'));

            return $this->_redirect('*/*/index');
        }

        $dataLoader = $this->dataLoader;
        $dataLoader->load($template);

        $this->setPageHelpLink('https://docs-m2.m2epro.com/shipping-policy-for-otto');

        if ($template->getId()) {
            $headerText =
                __(
                    'Edit "%template_title" Shipping Policy',
                    [
                        'template_title' => \M2E\Otto\Helper\Data::escapeHtml($template->getTitle()),
                    ],
                );
        } else {
            $headerText = __('Add Shipping Policy');
        }

        $content = $this->getLayout()->createBlock(
            \M2E\Otto\Block\Adminhtml\Otto\Template\Edit::class,
            '',
            [
                'data' => [
                    'template_nick' => \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_SHIPPING,
                ],
            ]
        );

        $content->toHtml();

        $this->getResult()->getConfig()->getTitle()->prepend($headerText);
        $this->addContent($content);

        return $this->getResult();
    }
}
