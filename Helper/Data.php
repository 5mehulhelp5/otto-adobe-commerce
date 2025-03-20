<?php

declare(strict_types=1);

namespace M2E\Otto\Helper;

class Data
{
    public const CUSTOM_IDENTIFIER = 'otto_extension';

    private \Magento\Framework\Module\Dir $dir;
    private \Magento\Backend\Model\UrlInterface $urlBuilder;
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(
        \Magento\Framework\Module\Dir $dir,
        \Magento\Backend\Model\UrlInterface $urlBuilder,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->dir = $dir;
        $this->urlBuilder = $urlBuilder;
        $this->objectManager = $objectManager;
    }

    /**
     * @deprecated
     * @param string $class
     *
     * @return array
     * @throws \M2E\Otto\Model\Exception
     * @throws \ReflectionException
     */
    public static function getClassConstants(string $class): array
    {
        $class = '\\' . ltrim($class, '\\');

        if (stripos($class, '\M2E\Otto\\') === false) {
            throw new \M2E\Otto\Model\Exception('Class name must begin with "\M2E\Otto"');
        }

        $reflectionClass = new \ReflectionClass($class);
        $tempConstants = $reflectionClass->getConstants();

        $constants = [];
        foreach ($tempConstants as $key => $value) {
            $constants[$class . '::' . strtoupper($key)] = $value;
        }

        return $constants;
    }

    /**
     * @param $controllerClass
     * @param array $params
     * @param bool $skipEnvironmentCheck
     * otto_config table may be missing if migration is going on, so trying to check environment will cause SQL
     *     error
     *
     * @return array
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getControllerActions($controllerClass, array $params = [], bool $skipEnvironmentCheck = false)
    {
        // fix for Magento2 sniffs that forcing to use ::class
        $controllerClass = str_replace('_', '\\', $controllerClass);

        $classRoute = str_replace('\\', '_', $controllerClass);
        $classRoute = implode('_', array_map(function ($item) {
            return $item === 'Otto' ? 'otto' : lcfirst($item);
        }, explode('_', $classRoute)));

        $moduleHelper = $this->objectManager->get(\M2E\Otto\Helper\Module::class);
        if ($skipEnvironmentCheck || !$moduleHelper->isDevelopmentEnvironment()) {
            $cachedActions = $this->objectManager->get(\M2E\Otto\Helper\Data\Cache\Permanent::class)
                                                 ->getValue('controller_actions_' . $classRoute);

            if ($cachedActions !== null) {
                return $this->getActionsUrlsWithParameters($cachedActions, $params);
            }
        }

        $controllersDir = $this->dir->getDir(
            \M2E\Otto\Helper\Module::IDENTIFIER,
            \Magento\Framework\Module\Dir::MODULE_CONTROLLER_DIR
        );
        $controllerDir = $controllersDir . '/Adminhtml/' . str_replace('\\', '/', $controllerClass);

        $actions = [];
        $controllerActions = array_diff(scandir($controllerDir), ['..', '.']);

        foreach ($controllerActions as $controllerAction) {
            $temp = explode('.php', $controllerAction);

            if (!empty($temp)) {
                $action = $temp[0];
                $action[0] = strtolower($action[0]);

                $actions[] = $classRoute . '/' . $action;
            }
        }

        if ($skipEnvironmentCheck || !$moduleHelper->isDevelopmentEnvironment()) {
            $this->objectManager->get(\M2E\Otto\Helper\Data\Cache\Permanent::class)
                                ->setValue('controller_actions_' . $classRoute, $actions);
        }

        return $this->getActionsUrlsWithParameters($actions, $params);
    }

    /**
     * @param array $actions
     * @param array $parameters
     *
     * @return array
     */
    private function getActionsUrlsWithParameters(array $actions, array $parameters = []): array
    {
        $actionsUrls = [];
        foreach ($actions as $route) {
            $url = $this->urlBuilder->getUrl('*/' . $route, $parameters);
            $actionsUrls[$route] = $url;
        }

        return $actionsUrls;
    }
}
