<?php

namespace M2E\Otto\Model\ActiveRecord;

class Factory
{
    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $objectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $modelName
     *
     * @return \M2E\Otto\Model\ActiveRecord\AbstractModel
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getObject($modelName)
    {
        // fix for Magento2 sniffs that forcing to use ::class
        $modelName = str_replace('_', '\\', $modelName);

        $model = $this->objectManager->create('\M2E\Otto\Model\\' . $modelName);

        if (!$model instanceof \M2E\Otto\Model\ActiveRecord\AbstractModel) {
            throw new \M2E\Otto\Model\Exception\Logic(
                __('%1 doesn\'t extends \M2E\Otto\Model\ActiveRecord\AbstractModel', $modelName)
            );
        }

        return $model;
    }

    /**
     * @param string $modelName
     * @param mixed $value
     * @param null|string $field
     * @param boolean $throwException
     *
     * @return \M2E\Otto\Model\ActiveRecord\AbstractModel|NULL
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getObjectLoaded($modelName, $value, $field = null, $throwException = true)
    {
        try {
            return $this->getObject($modelName)->load($value, $field);
        } catch (\M2E\Otto\Model\Exception\Logic $e) {
            if ($throwException) {
                throw $e;
            }

            return null;
        }
    }
}
