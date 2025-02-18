<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Manual;

trait SkipMessageTrait
{
    private function createSkipListMessage(): string
    {
        return (string)__(
            'Item(s) were not listed. The List rules set in Synchronization Policy are not met.'
        );
    }

    private function createManualSkipListMessage(): string
    {
        return (string)__(
            'Please use the \'Relist\' action instead of \'List\' to update \'Stopped\' products on the channel.'
        );
    }

    private function createSkipReviseMessage(): string
    {
        return (string)__(
            'Item(s) were not revised. No relevant product changes were detected to be updated on the channel.'
        );
    }

    private function createManualSkipReviseMessage(): string
    {
        return (string)__(
            'Please use the \'Relist\' action instead of \'Revise\' to update \'Inactive\' products on the channel.'
        );
    }

    private function createSkipRelistMessage(): string
    {
        return (string)__(
            'Item(s) were not relisted. The Relist rules set in Synchronization Policy are not met.'
        );
    }

    private function createSkipStopMessage(): string
    {
        return (string)__(
            'Item(s) were not stopped. The Stop rules set in Synchronization Policy are not met.'
        );
    }
}
