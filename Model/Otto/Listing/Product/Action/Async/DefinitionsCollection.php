<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Async;

use M2E\Otto\Model\Otto\Listing\Product\Action\Type as ActionType;

class DefinitionsCollection
{
    public const ACTION_REVISE = 'revise';
    public const ACTION_STOP = 'stop';
    public const ACTION_DELETE = 'delete';
    public const ACTION_RELIST = 'relist';
    public const ACTION_LIST = 'list';

    private const MAP = [
        self::ACTION_REVISE => [
            'start' => ActionType\Revise\ProcessStart::class,
            'end' => ActionType\Revise\ProcessEnd::class,
        ],
        self::ACTION_STOP => [
            'start' => ActionType\Stop\ProcessStart::class,
            'end' => ActionType\Stop\ProcessEnd::class,
        ],
        self::ACTION_RELIST => [
            'start' => ActionType\Relist\ProcessStart::class,
            'end' => ActionType\Relist\ProcessEnd::class,
        ],
        self::ACTION_LIST => [
            'start' => ActionType\ListAction\ProcessStart::class,
            'end' => ActionType\ListAction\ProcessEnd::class,
        ],
    ];

    public function has(string $nick): bool
    {
        return isset(self::MAP[$nick]);
    }

    public function getStart(string $nick): string
    {
        return self::MAP[$nick]['start'];
    }

    public function getEnd(string $nick): string
    {
        return self::MAP[$nick]['end'];
    }
}
