<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Order\Receive;

use M2E\Otto\Model\Otto\Connector\Order\Receive\Response;

trait CommandTrait
{
    public function getCommand(): array
    {
        return ['order', 'get', 'items'];
    }

    public function parseResponse(
        \M2E\Otto\Model\Connector\Response $response
    ): \M2E\Otto\Model\Otto\Connector\Order\Receive\Response {
        $responseData = $response->getResponseData();

        $toDate = \M2E\Otto\Helper\Date::createDateGmt(
            $responseData['to_update_date'] ?? $responseData['to_create_date'],
        );

        return new Response(
            $responseData['orders'],
            $toDate,
            $response->getMessageCollection()
        );
    }
}
