<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\DataProvider;

class VatProvider implements DataBuilderInterface
{
    use DataBuilderHelpTrait;

    public const NICK = 'Vat';

    public function getVat(): string
    {
        return 'FULL'; // currently only FULL allowed
    }
}
