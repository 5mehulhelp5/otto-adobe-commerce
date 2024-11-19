<?php

namespace M2E\Otto\Model\Issue\Notification;

use M2E\Otto\Model\Issue\DataObject;

interface ChannelInterface
{
    /**
     * @param DataObject $message
     *
     * @return void
     */
    public function addMessage(DataObject $message): void;
}
