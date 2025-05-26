<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Template\Edit;

class Form extends \M2E\Otto\Block\Adminhtml\Magento\Form\AbstractForm
{
    private \M2E\Otto\Helper\Data\GlobalData $globalDataHelper;
    private \M2E\Otto\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\Otto\Model\Account\Repository $accountRepository,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\Otto\Helper\Data\GlobalData $globalDataHelper,
        array $data = []
    ) {
        $this->accountRepository = $accountRepository;
        $this->globalDataHelper = $globalDataHelper;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('OttoTemplateEditForm');
        // ---------------------------------------

        $this->css->addFile('otto/template.css');
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form',
                'action' => 'javascript:void(0)',
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ],
        ]);

        $fieldset = $form->addFieldset(
            'general_fieldset',
            ['legend' => __('General'), 'collapsable' => false]
        );

        $templateData = $this->getTemplateData();

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'title' => __('Title'),
                'value' => $templateData['title'],
                'class' => 'input-text validate-title-uniqueness',
                'required' => true,
            ]
        );

        $templateNick = $this->getTemplateNick();
        if ($templateNick == \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_SHIPPING) {
            if (!empty($templateData['account_id'])) {
                $fieldset->addField(
                    'account_id_hidden',
                    'hidden',
                    [
                        'name' => 'shipping[account_id]',
                        'value' => $templateData['account_id'],
                    ]
                );
            }

            $fieldset->addField(
                'account_id',
                'select',
                [
                    'name' => 'shipping[account_id]',
                    'label' => __('Account'),
                    'title' => __('Account'),
                    'values' => $this->getAccountOptions(),
                    'value' => $templateData['account_id'],
                    'required' => true,
                    'disabled' => !empty($templateData['account_id']),
                ]
            );
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return $this;
    }

    public function getTemplateData()
    {
        $accountId = $this->getRequest()->getParam('account_id', false);

        $nick = $this->getTemplateNick();
        $templateData = $this->globalDataHelper->getValue("otto_template_{$nick}");

        return array_merge([
            'title' => '',
            'account_id' => ($accountId !== false) ? $accountId : '',
        ], $templateData->getData());
    }

    public function getTemplateNick()
    {
        return $this->getParentBlock()->getTemplateNick();
    }

    public function getTemplateId()
    {
        $template = $this->getParentBlock()->getTemplateObject();

        return $template ? $template->getId() : null;
    }

    private function getAccountOptions(): array
    {
        $accounts = $this->accountRepository->getAll();

        $optionsResult = [
            ['value' => '', 'label' => ''],
        ];
        foreach ($accounts as $account) {
            $optionsResult[] = [
                'value' => $account->getId(),
                'label' => $account->getTitle(),
            ];
        }

        return $optionsResult;
    }

    protected function _toHtml()
    {
        $nick = $this->getTemplateNick();
        $this->jsUrl->addUrls([
            'otto_template/getTemplateHtml' => $this->getUrl(
                '*/otto_template/getTemplateHtml',
                [
                    'account_id' => null,
                    'id' => $this->getTemplateId(),
                    'nick' => $nick,
                    'mode' => \M2E\Otto\Model\Otto\Template\Manager::MODE_TEMPLATE,
                    'data_force' => true,
                ]
            ),
            'otto_template/isTitleUnique' => $this->getUrl(
                '*/otto_template/isTitleUnique',
                [
                    'id' => $this->getTemplateId(),
                    'nick' => $nick,
                ]
            ),
            'deleteAction' => $this->getUrl(
                '*/Otto_template/delete',
                [
                    'id' => $this->getTemplateId(),
                    'nick' => $nick,
                ]
            ),
        ]);

        $this->jsTranslator->addTranslations([
            'Policy Title is not unique.' => __('Policy Title is not unique.'),
            'Do not show any more' => __('Do not show this message anymore'),
            'Save Policy' => __('Save Policy'),
        ]);

        $this->js->addRequireJs(
            [
                'form' => 'Otto/Otto/Template/Edit/Form',
                'jquery' => 'jquery',
            ],
            <<<JS

        window.OttoTemplateEditObj = new OttoTemplateEdit();
        OttoTemplateEditObj.templateNick = '{$this->getTemplateNick()}';
        OttoTemplateEditObj.initObservers();
JS
        );

        return parent::_toHtml();
    }
}
