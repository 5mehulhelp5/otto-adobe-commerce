<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Order\Receive;

class ItemsByUpdateDateCommand implements \M2E\Otto\Model\Connector\CommandInterface
{
    use CommandTrait;

    private \DateTimeInterface $updateFrom;
    private \DateTimeInterface $updateTo;
    private string $accountHash;

    public function __construct(
        string $accountHash,
        \DateTimeInterface $updateFrom,
        \DateTimeInterface $updateTo
    ) {
        $this->accountHash = $accountHash;
        $this->updateFrom = $updateFrom;
        $this->updateTo = $updateTo;
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'from_update_date' => $this->updateFrom->format('Y-m-d H:i:s'),
            'to_update_date' => $this->updateTo->format('Y-m-d H:i:s'),
        ];
    }
}
