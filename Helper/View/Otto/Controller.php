<?php

declare(strict_types=1);

namespace M2E\Otto\Helper\View\Otto;

class Controller
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;
    private \M2E\Otto\Model\Issue\Notification\Channel\Magento\Session $notificationSession;

    public function __construct(
        \M2E\Otto\Model\Issue\Notification\Channel\Magento\Session $notificationSession,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
        $this->notificationSession = $notificationSession;
    }

    public function addMessages(): void
    {
        $issueLocators = [
            \M2E\Otto\Model\Account\Issue\ValidTokens::class,
            \M2E\Otto\Model\Module\Issue\NewVersion::class,
        ];

        foreach ($issueLocators as $locator) {
            /** @var \M2E\Otto\Model\Issue\LocatorInterface $locatorModel */
            $locatorModel = $this->objectManager->create($locator);

            foreach ($locatorModel->getIssues() as $issue) {
                $this->notificationSession->addMessage($issue);
            }
        }
    }
}
