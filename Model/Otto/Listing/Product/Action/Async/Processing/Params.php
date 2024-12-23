<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Async\Processing;

class Params
{
    private int $listingProductId;
    private int $actionLogId;
    private int $actionLog;
    private int $initiator;
    private string $actionNick;
    private array $actionStartParams;
    private array $requestMetadata;
    private array $configuratorData;
    private int $statusChanger;
    private array $warningMessages;

    public function toArray(): array
    {
        return [
            'listing_product_id' => $this->listingProductId,
            'action_log_id' => $this->getActionLogId(),
            'action_log' => $this->getActionLog(),
            'initiator' => $this->getInitiator(),
            'action_nick' => $this->getActionNick(),
            'action_start_params' => $this->getActionStartParams(),
            'request_metadata' => $this->getRequestMetadata(),
            'configurator_data' => $this->getConfiguratorData(),
            'status_changer' => $this->getStatusChanger(),
            'warning_messages' => $this->getWarningMessages()
        ];
    }

    public static function tryFromArray(array $data): self
    {
        if (
            !isset(
                $data['listing_product_id'],
                $data['action_log_id'],
                $data['action_log'],
                $data['initiator'],
                $data['action_nick'],
                $data['action_start_params'],
                $data['request_metadata'],
                $data['configurator_data'],
                $data['status_changer']
            )
        ) {
            throw new \M2E\Otto\Model\Exception\Logic('Processing params are not valid.');
        }

        return new self(
            (int)$data['listing_product_id'],
            (int)$data['action_log_id'],
            (int)$data['action_log'],
            (int)$data['initiator'],
            $data['action_nick'],
            $data['action_start_params'],
            $data['request_metadata'],
            $data['configurator_data'],
            $data['status_changer'],
            $data['warning_messages'] ?? []
        );
    }

    public function __construct(
        int $listingProductId,
        int $actionLogId,
        int $actionLog,
        int $initiator,
        string $actionNick,
        array $actionStartParams,
        array $requestMetadata,
        array $configuratorData,
        int $statusChanger,
        array $warningMessages
    ) {
        $this->listingProductId = $listingProductId;
        $this->actionLogId = $actionLogId;
        $this->actionLog = $actionLog;
        $this->initiator = $initiator;
        $this->actionNick = $actionNick;
        $this->actionStartParams = $actionStartParams;
        $this->requestMetadata = $requestMetadata;
        $this->configuratorData = $configuratorData;
        $this->statusChanger = $statusChanger;
        $this->warningMessages = $warningMessages;
    }

    public function getListingProductId(): int
    {
        return $this->listingProductId;
    }

    public function getActionLogId(): int
    {
        return $this->actionLogId;
    }

    public function getActionLog(): int
    {
        return $this->actionLog;
    }

    public function getInitiator(): int
    {
        return $this->initiator;
    }

    public function getActionNick(): string
    {
        return $this->actionNick;
    }

    public function getActionStartParams(): array
    {
        return $this->actionStartParams;
    }

    public function getRequestMetadata(): array
    {
        return $this->requestMetadata;
    }

    public function getConfiguratorData(): array
    {
        return $this->configuratorData;
    }

    public function getStatusChanger(): int
    {
        return $this->statusChanger;
    }

    public function getWarningMessages(): array
    {
        return $this->warningMessages;
    }
}
