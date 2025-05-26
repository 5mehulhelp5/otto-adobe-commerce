<?php

declare(strict_types=1);

namespace M2E\Otto\Plugin\Menu\Magento\Backend\Model\Menu;

use M2E\Otto\Helper\Module;
use M2E\Otto\Helper\View\Otto;
use M2E\Otto\Helper\Module\Maintenance as Maintenance;

class Config extends \M2E\Otto\Plugin\AbstractPlugin
{
    public const MENU_STATE_REGISTRY_KEY = '/menu/state/';
    public const MAINTENANCE_MENU_STATE_CACHE_KEY = 'maintenance_menu_state';

    private \Magento\Backend\Model\Menu\Item\Factory $itemFactory;
    private \M2E\Otto\Model\Registry\Manager $registry;
    private bool $isProcessed = false;

    public function __construct(
        \M2E\Otto\Model\Registry\Manager $registry,
        \Magento\Backend\Model\Menu\Item\Factory $itemFactory
    ) {
        $this->itemFactory = $itemFactory;
        $this->registry = $registry;
    }

    protected function canExecute(): bool
    {
        /** @var \M2E\Otto\Helper\Module $helper */
        $helper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\Otto\Helper\Module::class
        );

        return $helper->areImportantTablesExist();
    }

    public function aroundGetMenu(\Magento\Backend\Model\Menu\Config $interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('getMenu', $interceptor, $callback, $arguments);
    }

    protected function processGetMenu(
        \Magento\Backend\Model\Menu\Config $interceptor,
        \Closure $callback,
        array $arguments
    ) {
        /** @var \Magento\Backend\Model\Menu $menuModel */
        $menuModel = $callback(...$arguments);

        if ($this->isProcessed) {
            return $menuModel;
        }

        $this->isProcessed = true;

        /** @var \M2E\Otto\Helper\Data\Cache\Permanent $cachePermanentHelper */
        $cachePermanentHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\Otto\Helper\Data\Cache\Permanent::class
        );

        $maintenanceMenuState = $cachePermanentHelper->getValue(
            self::MAINTENANCE_MENU_STATE_CACHE_KEY
        );

        /** @var \M2E\Core\Helper\Magento $helper */
        $magentoHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\Core\Helper\Magento::class
        );

        /** @var \M2E\Otto\Helper\Module\Maintenance $maintenanceHelper */
        $maintenanceHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\Otto\Helper\Module\Maintenance::class
        );

        if ($maintenanceHelper->isEnabled()) {
            if ($maintenanceMenuState === null) {
                $cachePermanentHelper->setValue(
                    self::MAINTENANCE_MENU_STATE_CACHE_KEY,
                    true
                );
                $magentoHelper->clearMenuCache();
            }
            $this->processMaintenance($menuModel);

            return $menuModel;
        }

        if ($maintenanceMenuState !== null) {
            $cachePermanentHelper->removeValue(
                self::MAINTENANCE_MENU_STATE_CACHE_KEY
            );
            $magentoHelper->clearMenuCache();
        }

        $currentMenuState = $this->buildMenuStateData();
        $previousMenuState = $this->registry->getValueFromJson(self::MENU_STATE_REGISTRY_KEY);

        if ($previousMenuState != $currentMenuState) {
            $this->registry->setValue(
                self::MENU_STATE_REGISTRY_KEY,
                json_encode($currentMenuState, JSON_THROW_ON_ERROR)
            );
            $magentoHelper->clearMenuCache();
        }

        /** @var \M2E\Otto\Helper\Module $moduleHelper */
        $moduleHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\Otto\Helper\Module::class
        );

        if ($moduleHelper->isDisabled()) {
            $this->processModuleDisable($menuModel);

            return $menuModel;
        }

        $this->processWizard($menuModel->get(Otto::MENU_ROOT_NODE_NICK), Otto::NICK);

        return $menuModel;
    }

    private function processMaintenance(\Magento\Backend\Model\Menu $menuModel)
    {
        $menuModelItem = $menuModel->get(Otto::MENU_ROOT_NODE_NICK);

        if ($menuModelItem !== null && $menuModelItem->isAllowed()) {
            $maintenanceMenuItemResource = Otto::MENU_ROOT_NODE_NICK;
        }

        foreach ($menuModel as $menuIndex => $menuItem) {
            if ($menuItem->getId() == $maintenanceMenuItemResource) {
                $maintenanceMenuItem = $this->itemFactory->create([
                    'id' => Maintenance::MENU_ROOT_NODE_NICK,
                    'module' => Module::IDENTIFIER,
                    'title' => \M2E\Otto\Helper\Module::getChannelTitle(),
                    'resource' => $maintenanceMenuItemResource,
                    'action' => 'm2e_otto/maintenance',
                ]);

                $menuModel->remove($maintenanceMenuItemResource);
                $menuModel->add($maintenanceMenuItem, null, $menuIndex);
                break;
            }
        }

        $this->processModuleDisable($menuModel);
    }

    private function processModuleDisable(\Magento\Backend\Model\Menu $menuModel)
    {
        $menuModel->remove(Otto::MENU_ROOT_NODE_NICK);
    }

    private function processWizard(?\Magento\Backend\Model\Menu\Item $menu, string $viewNick): void
    {
        if ($menu === null) {
            return;
        }

        /** @var \M2E\Otto\Helper\Module\Wizard $wizard */
        $wizard = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\Otto\Helper\Module\Wizard::class
        );
        $activeBlocker = $wizard->getActiveBlockerWizard($viewNick);

        if ($activeBlocker === null) {
            return;
        }

        $menu->getChildren()->exchangeArray([]);

        $actionUrl = 'm2e_otto/wizard_' . $activeBlocker->getNick();
        $menu->setAction($actionUrl);
    }

    private function buildMenuStateData(): array
    {
        /** @var \M2E\Otto\Helper\Module $moduleHelper */
        $moduleHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\Otto\Helper\Module::class
        );

        /** @var \M2E\Otto\Helper\Module\Wizard $wizardHelper */
        $wizardHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\Otto\Helper\Module\Wizard::class
        );

        return [
            Module::IDENTIFIER => [
                $moduleHelper->isDisabled(),
            ],
            Otto::MENU_ROOT_NODE_NICK => [
                $wizardHelper->getActiveBlockerWizard(Otto::NICK) === null,
            ],
        ];
    }
}
