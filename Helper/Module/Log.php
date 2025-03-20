<?php

declare(strict_types=1);

namespace M2E\Otto\Helper\Module;

class Log
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $string
     * @param array $params
     * @param array $links
     *
     * @return string
     * @throws \JsonException
     */
    public static function encodeDescription(string $string, array $params = [], array $links = []): string
    {
        if (empty($params) && empty($links)) {
            return $string;
        }

        $descriptionData = [
            'string' => $string,
            'params' => $params,
            'links' => $links,
        ];

        return json_encode($descriptionData, JSON_THROW_ON_ERROR);
    }

    /**
     * @param mixed $data
     *
     * @return string
     */
    public static function decodeDescription($data): string
    {
        if (!is_string($data) || $data === '') {
            return '';
        }

        if ($data[0] !== '{') {
            return (string)__($data);
        }

        $descriptionData = json_decode($data, true);
        $string = (string)__($descriptionData['string']);

        if (!empty($descriptionData['params'])) {
            $string = self::addPlaceholdersToMessage($string, $descriptionData['params']);
        }

        if (!empty($descriptionData['links'])) {
            $string = self::addLinksToMessage($string, $descriptionData['links']);
        }

        return $string;
    }

    private static function addPlaceholdersToMessage(string $string, array $params): string
    {
        foreach ($params as $key => $value) {
            if (isset($value[0]) && $value[0] === '{') {
                $tempValueArray = json_decode($value, true);
                if (is_array($tempValueArray)) {
                    $value = self::decodeDescription($value);
                }
            }

            if ($key[0] === '!') {
                $key = substr($key, 1);
            } else {
                $value = (string)__($value);
            }

            $string = str_replace('%' . $key . '%', (string)$value, $string);
        }

        return $string;
    }

    private static function addLinksToMessage(string $string, array $links): string
    {
        $readMoreLinks = [];
        $resultString = $string;

        foreach ($links as $link) {
            preg_match('/!\w*_start!/', $resultString, $foundedStartMatches);

            if (empty($foundedStartMatches)) {
                $readMoreLinks[] = $link;
                continue;
            }

            $startPart = $foundedStartMatches[0];
            $endPart = str_replace('start', 'end', $startPart);

            $wasFoundEndMatches = strpos($resultString, $endPart);

            if ($wasFoundEndMatches !== false) {
                $openLinkTag = '<a href="' . $link . '" target="_blank">';
                $closeLinkTag = '</a>';

                $resultString = str_replace($startPart, $openLinkTag, $resultString);
                $resultString = str_replace($endPart, $closeLinkTag, $resultString);
            } else {
                $readMoreLinks[] = $link;
            }
        }

        if (!empty($readMoreLinks)) {
            foreach ($readMoreLinks as &$link) {
                $link = '<a href="' . $link . '" target="_blank">' . __('here') . '</a>';
            }

            $readMoreString = __('Details') . ' ' . implode(' ' . __('or') . ' ', $readMoreLinks) . '.';

            $resultString .= ' ' . $readMoreString;
        }

        return $resultString;
    }

    public static function getActionsTitlesByClass(string $class): array
    {
        switch ($class) {
            case \M2E\Otto\Model\Listing\Log::class:
                $prefix = 'ACTION_';
                break;

            case \M2E\Otto\Model\Synchronization\Log::class:
                $prefix = 'TASK_';
                break;
        }

        $reflectionClass = new \ReflectionClass($class);
        $tempConstants = $reflectionClass->getConstants();

        $actionsNames = [];
        foreach ($tempConstants as $key => $value) {
            if (substr($key, 0, strlen($prefix)) == $prefix) {
                $actionsNames[$key] = $value;
            }
        }

        $actionsValues = [];
        foreach ($actionsNames as $action => $valueAction) {
            foreach ($tempConstants as $key => $valueConstant) {
                if ($key === '_' . $action) {
                    $actionsValues[$valueAction] = __($valueConstant);
                }
            }
        }

        return $actionsValues;
    }

    /**
     * @param $resultType
     *
     * @return mixed
     */
    public function getStatusByResultType($resultType)
    {
        $typesStatusesMap = [
            \M2E\Otto\Model\Log\AbstractModel::TYPE_INFO => \M2E\Core\Helper\Data::STATUS_SUCCESS,
            \M2E\Otto\Model\Log\AbstractModel::TYPE_SUCCESS => \M2E\Core\Helper\Data::STATUS_SUCCESS,
            \M2E\Otto\Model\Log\AbstractModel::TYPE_WARNING => \M2E\Core\Helper\Data::STATUS_WARNING,
            \M2E\Otto\Model\Log\AbstractModel::TYPE_ERROR => \M2E\Core\Helper\Data::STATUS_ERROR,
        ];

        return $typesStatusesMap[$resultType];
    }

    public function platformInfo(): string
    {
        $platformInfo = [];
        $platformInfo['edition'] = $this->objectManager->get(\M2E\Core\Helper\Magento::class)->getEditionName();
        $platformInfo['version'] = $this->objectManager->get(\M2E\Core\Helper\Magento::class)->getVersion();

        return <<<DATA
-------------------------------- PLATFORM INFO -----------------------------------
Edition: {$platformInfo['edition']}
Version: {$platformInfo['version']}

DATA;
    }

    public function moduleInfo(): string
    {
        $moduleInfo = [];
        $moduleInfo['name'] = $this->objectManager->get(\M2E\Otto\Model\Module::class)->getName();
        $moduleInfo['version'] = $this->objectManager->get(\M2E\Otto\Model\Module::class)->getPublicVersion();

        return <<<DATA
-------------------------------- MODULE INFO -------------------------------------
Name: {$moduleInfo['name']}
Version: {$moduleInfo['version']}

DATA;
    }
}
