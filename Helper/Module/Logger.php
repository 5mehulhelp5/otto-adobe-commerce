<?php

declare(strict_types=1);

namespace M2E\Otto\Helper\Module;

class Logger
{
    private \M2E\Otto\Model\Log\SystemFactory $logSystemFactory;

    /**
     * @param \M2E\Otto\Model\Log\SystemFactory $logSystemFactory
     */
    public function __construct(
        \M2E\Otto\Model\Log\SystemFactory $logSystemFactory
    ) {
        $this->logSystemFactory = $logSystemFactory;
    }

    /**
     * @param mixed $logData
     * @param string $class
     *
     * @return void
     */
    public function process($logData, string $class = 'undefined'): void
    {
        try {
            $info = $this->getLogMessage($logData, $class);
            $info .= $this->getStackTraceInfo();

            $this->systemLog($class, null, $info);
        } catch (\Exception $exceptionTemp) {
        }
    }

    /**
     * @param string $class
     * @param string|null $message
     * @param string $description
     *
     * @return void
     */
    private function systemLog(string $class, ?string $message, string $description): void
    {
        $log = $this->logSystemFactory->create();
        $log->setData(
            [
                'type' => \M2E\Otto\Model\Log\System::TYPE_LOGGER,
                'class' => $class,
                'description' => $message,
                'detailed_description' => $description,
            ],
        );
        $log->save();
    }

    /**
     * @param mixed $logData
     * @param string $type
     *
     * @return string
     */
    private function getLogMessage($logData, string $type): string
    {
        if ($logData instanceof \Magento\Framework\Phrase) {
            $logData = (string)$logData;
        }

        if (!is_string($logData)) {
            $logData = print_r($logData, true);
        }

        // @codingStandardsIgnoreLine
        return '[DATE] ' . date('Y-m-d H:i:s', (int)gmdate('U')) . PHP_EOL .
            '[TYPE] ' . $type . PHP_EOL .
            '[MESSAGE] ' . $logData . PHP_EOL .
            str_repeat('#', 80) . PHP_EOL . PHP_EOL;
    }

    /**
     * @return string
     */
    private function getStackTraceInfo(): string
    {
        $exception = new \Exception('');

        return <<<TRACE
-------------------------------- STACK TRACE INFO --------------------------------
{$exception->getTraceAsString()}

TRACE;
    }
}
