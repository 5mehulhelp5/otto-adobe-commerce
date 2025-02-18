<?php

namespace M2E\Otto\Model\Otto\Connector\Order\Packages;

class ShipCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private \M2E\Otto\Model\Account $account;
    /** @var \M2E\Otto\Model\Otto\Connector\Order\Packages\Ship\Package[] */
    private array $packages;

    /**
     * @param \M2E\Otto\Model\Otto\Connector\Order\Packages\Ship\Package[] $packages
     */
    public function __construct(
        \M2E\Otto\Model\Account $account,
        array $packages
    ) {
        $this->account = $account;
        $this->packages = $packages;
    }

    public function getCommand(): array
    {
        return ['order', 'send', 'entity'];
    }

    public function getRequestData(): array
    {
        $orders = [];
        foreach ($this->packages as $package) {
            $orders[] = $package->toArray();
        }

        return [
            'account' => $this->account->getServerHash(),
            'orders' => $orders,
        ];
    }

    public function parseResponse(
        \M2E\Core\Model\Connector\Response $response
    ): \M2E\Otto\Model\Otto\Connector\Order\Packages\Ship\Response {
        $responseData = $response->getResponseData();

        $errors = [];
        foreach ($responseData['orders'] as $data) {
            if (
                !array_key_exists('is_success', $data)
                || !array_key_exists('messages', $data)
            ) {
                throw new \M2E\Otto\Model\Exception\Logic('Response not valid.');
            }

            if ($data['is_success']) {
                continue;
            }

            foreach ($data['messages'] as $messageData) {
                $errors[] = new \M2E\Otto\Model\Otto\Connector\Order\Packages\Ship\Error(
                    $data['id'],
                    $messageData['text'],
                );
            }
        }

        return new \M2E\Otto\Model\Otto\Connector\Order\Packages\Ship\Response($errors);
    }
}
