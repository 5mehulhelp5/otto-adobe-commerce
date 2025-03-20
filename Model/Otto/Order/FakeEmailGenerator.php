<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Order;

class FakeEmailGenerator
{
    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function generate(string $firstName, string $lastName): string
    {
        $username = $this->makeUsername($firstName, $lastName);

        if (empty($username)) {
            throw new \M2E\Otto\Model\Exception\Logic('Empty email username');
        }

        return $username . \M2E\Core\Model\Magento\Customer::FAKE_EMAIL_POSTFIX;
    }

    private function makeUsername(string $firstName, string $lastName): string
    {
        return trim(
            sprintf(
                '%s_%s',
                $this->prepareNamePart($firstName),
                $this->prepareNamePart($lastName)
            ),
            ' _'
        );
    }

    private function prepareNamePart(string $namePart): string
    {
        return preg_replace(
            '/[^a-z0-9._%+-]/',
            '',
            mb_strtolower(trim($namePart))
        );
    }
}
