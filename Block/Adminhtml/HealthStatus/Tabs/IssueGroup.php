<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\HealthStatus\Tabs;

class IssueGroup extends \M2E\Otto\Block\Adminhtml\Magento\Form\AbstractForm
{
    public const NOTE_ELEMENT = \M2E\Otto\Block\Adminhtml\HealthStatus\Tabs\Element\Note::class;

    /** @var \M2E\Otto\Model\HealthStatus\Task\Result\Set */
    private $resultSet;

    //########################################

    public function __construct(
        \M2E\Otto\Model\HealthStatus\Task\Result\Set $resultSet,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->resultSet = $resultSet;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    //########################################

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();

        $createdFieldSets = [];
        foreach ($this->resultSet->getByKeys() as $resultItem) {
            if (in_array($resultItem->getFieldSetName(), $createdFieldSets)) {
                continue;
            }

            $fieldSet = $form->addFieldset(
                'fieldset_' . strtolower($resultItem->getFieldSetName()),
                [
                    'legend' => __($resultItem->getFieldSetName()),
                    'collapsable' => false,
                ]
            );

            foreach ($this->resultSet->getByFieldSet($this->resultSet->getFieldSetKey($resultItem)) as $byFieldSet) {
                $fieldSet->addField(
                    strtolower($byFieldSet->getTaskHash()),
                    self::NOTE_ELEMENT,
                    [
                        'label' => __($byFieldSet->getFieldName()),
                        'text' => $byFieldSet->getTaskMessage(),
                        'task_result' => $byFieldSet,
                    ]
                );
            }

            $createdFieldSets[] = $resultItem->getFieldSetName();
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }

    //########################################
}
