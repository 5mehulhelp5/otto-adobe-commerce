<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Template;

abstract class AbstractBuilder extends \M2E\Otto\Model\ActiveRecord\AbstractBuilder
{
    protected function prepareData(): array
    {
        $data = [];

        // ---------------------------------------
        if (isset($this->rawData['id']) && (int)$this->rawData['id'] > 0) {
            $data['id'] = (int)$this->rawData['id'];
        }

        $data['title'] = $this->rawData['title'];
        // ---------------------------------------

        // ---------------------------------------
        unset($this->rawData['id']);
        unset($this->rawData['title']);

        // ---------------------------------------

        return $data;
    }
}
