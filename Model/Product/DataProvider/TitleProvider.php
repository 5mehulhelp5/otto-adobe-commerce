<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\DataProvider;

class TitleProvider implements DataBuilderInterface
{
    use DataBuilderHelpTrait;

    public const NICK = 'Title';

    public function getTitle(\M2E\Otto\Model\Product $product): string
    {
        $title = $product->getDescriptionTemplateSource()->getTitle();

        if (strlen($title) > 70) {
            $title = substr($title, 0, 70);
        }

        return $title;
    }
}
