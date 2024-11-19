<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Connector\Response;

class MessageCollection
{
    /** @var \M2E\Otto\Model\Connector\Response\Message[] */
    private array $messages;

    /**
     * @param Message[] $messages
     */
    public function __construct(array $messages)
    {
        $this->messages = $messages;
    }

    public function hasErrorWithCode($code): bool
    {
        return $this->findErrorWithCode($code) !== null;
    }

    public function getErrorWithCode($code): Message
    {
        $error = $this->findErrorWithCode($code);
        if ($error === null) {
            throw new \LogicException(sprintf('Error with code %s not found.', $code));
        }

        return $error;
    }

    public function findErrorWithCode($code): ?Message
    {
        foreach ($this->getErrors() as $error) {
            if ($error->getCode() === $code) {
                return $error;
            }
        }

        return null;
    }

    public function hasErrors(): bool
    {
        return !empty($this->getErrors());
    }

    /**
     * @return Message[]
     */
    public function getErrors(): array
    {
        $messages = [];
        foreach ($this->messages as $message) {
            if ($message->isError()) {
                $messages[] = $message;
            }
        }

        return $messages;
    }

    public function hasWarnings(): bool
    {
        return !empty($this->getWarnings());
    }

    /**
     * @return Message[]
     */
    public function getWarnings(): array
    {
        $messages = [];
        foreach ($this->messages as $message) {
            if ($message->isWarning()) {
                $messages[] = $message;
            }
        }

        return $messages;
    }

    public function hasSystemErrors(): bool
    {
        return !empty($this->getSystemErrors());
    }

    /**
     * @return Message[]
     */
    public function getSystemErrors(): array
    {
        $messages = [];
        foreach ($this->getErrors() as $message) {
            if ($message->isSenderSystem()) {
                $messages[] = $message;
            }
        }

        return $messages;
    }

    public function getCombinedSystemErrorsString(): ?string
    {
        $messages = $this->getSystemErrors();

        return !empty($messages) ?
            implode(', ', array_map(static fn (Message $message) => $message->getText(), $messages)) :
            null;
    }

    /**
     * @return \M2E\Otto\Model\Connector\Response\Message[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
