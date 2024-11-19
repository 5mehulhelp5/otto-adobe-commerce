<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Type\Relist;

class Response extends \M2E\Otto\Model\Otto\Listing\Product\Action\Type\Revise\Response
{
    public function process(): void
    {
        $response = $this->getResponseData();

        if (!$this->isSuccess()) {
            $this->addTags($response['products'][0]['messages']);

            return;
        }

        parent::process();
    }

    private function isSuccess(): bool
    {
        $responseData = $this->getResponseData();

        return !empty($responseData['products'][0]['is_success']);
    }

    /**
     * @throws \Magento\Framework\Currency\Exception\CurrencyException
     */
    public function generateResultMessage(): void
    {
        if (!$this->isSuccess()) {
            $responseData = $this->getResponseData();
            if (empty($responseData['products'][0]['messages'])) {
                $this->getLogBuffer()->addFail('Product failed to be relisted.');

                return;
            }

            $resultMessage = sprintf(
                'Product failed to be relisted. Reason: %s',
                $responseData['products'][0]['messages'][0]['title']
            );
            $this->getLogBuffer()->addFail($resultMessage);
        }

        $domainListingProduct = $this->getProduct();
        $onlineQty = $domainListingProduct->getOnlineQty();

        $currencyCode = $this->getProduct()->getCurrencyCode();
        $currency = $this->localeCurrency->getCurrency($currencyCode);

        $message = sprintf(
            'Product was Relisted with QTY %d, Price %s',
            $onlineQty,
            $currency->toCurrency($domainListingProduct->getOnlineCurrentPrice())
        );

        $this->getLogBuffer()->addSuccess($message);
    }
}
