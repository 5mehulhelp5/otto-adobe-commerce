<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Magento\Grid;

use Magento\Backend\Block\Widget\Grid\Extended;
use M2E\Otto\Block\Adminhtml\Traits;

abstract class AbstractGrid extends Extended
{
    use Traits\BlockTrait;
    use Traits\RendererTrait;

    /** @var string */
    protected $_template = 'magento/grid/extended.phtml';

    protected bool $customPageSize = false;

    /**
     * @param \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->css = $context->getCss();
        $this->jsPhp = $context->getJsPhp();
        $this->js = $context->getJs();
        $this->jsTranslator = $context->getJsTranslator();
        $this->jsUrl = $context->getJsUrl();

        parent::__construct($context, $backendHelper, $data);
    }

    public function addColumn($columnId, $column)
    {
        if (is_array($column)) {
            if (!array_key_exists('header_css_class', $column)) {
                $column['header_css_class'] = 'grid-listing-column-' . $columnId;
            }

            if (!array_key_exists('column_css_class', $column)) {
                $column['column_css_class'] = 'grid-listing-column-' . $columnId;
            }
        }

        if (is_array($column)) {
            $this->getColumnSet()->setChild(
                $columnId,
                $this->getLayout()
                     ->createBlock(\M2E\Otto\Block\Adminhtml\Widget\Grid\Column\Extended\Rewrite::class)
                     ->setData($column)
                     ->setId($columnId)
                     ->setGrid($this)
            );
            $this->getColumnSet()->getChildBlock($columnId)->setGrid($this);
        } else {
            throw new \Exception((string)__('Please correct the column format and try again.'));
        }

        $this->_lastColumnId = $columnId;

        return $this;
    }

    public function getMassactionBlockName()
    {
        return \M2E\Otto\Block\Adminhtml\Magento\Grid\Massaction::class;
    }

    public function isAllowedCustomPageSize()
    {
        return $this->customPageSize;
    }

    public function setId(string $id): self
    {
        $this->setData('id', $id);

        return $this;
    }

    public function setUseAjax(bool $useAjax): self
    {
        $this->setData('use_ajax', $useAjax);

        return $this;
    }

    public function setCustomPageSize($value)
    {
        $this->customPageSize = $value;

        return $this;
    }

    /**
     * @return void
     */
    public function applyQueryFilters(): void
    {
        // See \Magento\Backend\Block\Widget\Grid::_prepareCollection()
        $filter = $this->getParam($this->getVarNameFilter());

        if ($filter === null) {
            $filter = $this->_defaultFilter;
        }

        if (is_string($filter)) {
            $data = $this->_backendHelper->prepareFilterString($filter);
            $data = array_merge($data, (array)$this->getRequest()->getPost($this->getVarNameFilter()));
            $this->_setFilterValues($data);
        } elseif ($filter && is_array($filter)) {
            $this->_setFilterValues($filter);
        } elseif (count($this->_defaultFilter) !== 0) {
            $this->_setFilterValues($this->_defaultFilter);
        }
    }

    public function getCsv(): string
    {
        $csv = '';
        $this->_isExport = true;
        $this->_prepareGrid();
        $this->getCollection()->getSelect()->limit();
        $this->getCollection()->setPageSize(0);
        $this->getCollection()->load();
        $this->_afterLoadCollection();

        $data = [];
        foreach ($this->getColumns() as $column) {
            if (!$column->getIsSystem()) {
                $data[] = '"' . $column->getExportHeader() . '"';
            }
        }
        $csv .= implode(',', $data) . "\n";

        foreach ($this->getCollection() as $item) {
            $data = [];
            foreach ($this->getColumns() as $column) {
                if (!$column->getIsSystem()) {
                    $exportField = (string)$column->getRowFieldExport($item);
                    $data[] = '"' . str_replace(
                        ['"', '\\'],
                        ['""', '\\\\'],
                        is_numeric($exportField) ? $exportField : ($exportField ?: '')
                    ) . '"';
                }
            }
            $csv .= implode(',', $data) . "\n";
        }

        if ($this->getCountTotals()) {
            $data = [];
            foreach ($this->getColumns() as $column) {
                if (!$column->getIsSystem()) {
                    $data[] = '"' . str_replace(
                        ['"', '\\'],
                        ['""', '\\\\'],
                        $column->getRowFieldExport($this->getTotals()) ?: ''
                    ) . '"';
                }
            }
            $csv .= implode(',', $data) . "\n";
        }

        return $csv;
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Massaction
     */
    public function getMassactionBlock()
    {
        return parent::getMassactionBlock();
    }
}
