<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Order\Receive;

class ItemsByCreateDateCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    use CommandTrait;

    private \DateTimeInterface $createFrom;
    private \DateTimeInterface $createTo;
    private string $accountHash;

    public function __construct(
        string $accountHash,
        \DateTimeInterface $createFrom,
        \DateTimeInterface $createTo
    ) {
        $this->accountHash = $accountHash;
        $this->createFrom = $createFrom;
        $this->createTo = $createTo;
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'from_create_date' => $this->createFrom->format('Y-m-d H:i:s'),
            'to_create_date' => $this->createTo->format('Y-m-d H:i:s'),
        ];
    }
}
