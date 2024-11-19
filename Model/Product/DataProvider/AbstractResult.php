<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\DataProvider;

abstract class AbstractResult
{
    private bool $isSuccess;
    /** @var mixed */
    protected $value;
    private array $messages;

    private function __construct(
        bool $isSuccess,
        $value,
        array $messages
    ) {
        $this->isSuccess = $isSuccess;
        $this->value = $value;
        $this->messages = $messages;
    }

    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    abstract public function getValue();

    public function getMessages(): array
    {
        return $this->messages;
    }

    // ----------------------------------------

    /**
     * @param $value
     * @param array $warnings
     *
     * @return static
     */
    public static function success($value, array $warnings = [])
    {
        return new static(true, $value, $warnings);
    }

    /**
     * @param array $errors
     *
     * @return static
     */
    public static function error(array $errors)
    {
        return new static(false, null, $errors);
    }
}
