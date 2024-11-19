<?php

namespace M2E\Otto\Model\Issue;

interface LocatorInterface
{
    /**
     * @return DataObject[]
     */
    public function getIssues(): array;
}
