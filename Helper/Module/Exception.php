<?php

declare(strict_types=1);

namespace M2E\Otto\Helper\Module;

class Exception
{
    private bool $isRegisterFatalHandler = false;
    private string $systemLogTableName;

    private Log $logHelper;
    private \Magento\Framework\App\ResourceConnection $resourceConnection;
    private \Magento\Store\Model\StoreManagerInterface $storeManager;
    private \M2E\Otto\Model\Log\SystemFactory $systemLogFactory;
    private \M2E\Otto\Model\ResourceModel\Log\System $systemLogResource;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Log\System $systemLogResource,
        \M2E\Otto\Model\Log\SystemFactory $systemLogFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        Log $logHelper
    ) {
        $this->logHelper = $logHelper;
        $this->storeManager = $storeManager;
        $this->resourceConnection = $resourceConnection;
        $this->systemLogFactory = $systemLogFactory;
        $this->systemLogResource = $systemLogResource;
    }

    public function process(\Throwable $throwable, array $context = []): void
    {
        $class = get_class($throwable);
        $info = $this->getExceptionDetailedInfo($throwable, $context);

        $type = \M2E\Otto\Model\Log\System::TYPE_EXCEPTION;
        if ($throwable instanceof \M2E\Core\Model\Exception\Connection) {
            $type = \M2E\Otto\Model\Log\System::TYPE_EXCEPTION_CONNECTOR;
        }

        $this->systemLog(
            $type,
            $class,
            $throwable->getMessage(),
            $info,
        );
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getExceptionDetailedInfo(\Throwable $throwable, array $context = []): string
    {
        $info = $this->getExceptionInfo($throwable, get_class($throwable), $context);
        $info .= $this->getExceptionStackTraceInfo($throwable);
        $info .= $this->getAdditionalActionInfo();
        $info .= $this->logHelper->platformInfo();
        $info .= $this->logHelper->moduleInfo();

        return $info;
    }

    private function getExceptionInfo(\Throwable $throwable, string $type, array $context): string
    {
        $additionalData = $throwable instanceof \M2E\Otto\Model\Exception ? $throwable->getAdditionalData() : [];
        $additionalData = array_merge($additionalData, $context);
        $additionalData = print_r($additionalData, true);

        return <<<EXCEPTION
-------------------------------- EXCEPTION INFO ----------------------------------
Type: {$type}
File: {$throwable->getFile()}
Line: {$throwable->getLine()}
Code: {$throwable->getCode()}
Message: {$throwable->getMessage()}
Additional Data: {$additionalData}

EXCEPTION;
    }

    private function getExceptionStackTraceInfo(\Throwable $throwable): string
    {
        return <<<TRACE
-------------------------------- STACK TRACE INFO --------------------------------
{$throwable->getTraceAsString()}

TRACE;
    }

    // ----------------------------------------

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getAdditionalActionInfo(): string
    {
        $currentStoreId = $this->storeManager->getStore()->getId();

        return <<<ACTION
-------------------------------- ADDITIONAL INFO -------------------------------------
Current Store: {$currentStoreId}

ACTION;
    }

    /**
     * @param int $type
     * @param string $class
     * @param string $message
     * @param string $description
     *
     * @return void
     */
    private function systemLog(int $type, string $class, string $message, string $description): void
    {
        // @codingStandardsIgnoreLine
        $trace = debug_backtrace();
        $file = $trace[1]['file'] ?? 'not set';
        $line = $trace[1]['line'] ?? 'not set';

        $additionalData = [
            'called-from' => $file . ' : ' . $line,
        ];

        $log = $this->systemLogFactory->create();
        $log->setData(
            [
                'type' => $type,
                'class' => $class,
                'description' => $message,
                'detailed_description' => $description,
                // @codingStandardsIgnoreLine
                'additional_data' => print_r($additionalData, true),
            ],
        );
        $log->save();
    }

    // ----------------------------------------

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setFatalErrorHandler(): void
    {
        if ($this->isRegisterFatalHandler) {
            return;
        }

        $this->isRegisterFatalHandler = true;

        $this->systemLogTableName = $this->systemLogResource->getMainTable();

        $shutdownFunction = function () {
            $error = error_get_last();

            if ($error === null) {
                return;
            }

            $fatalErrors = [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR];

            if (in_array($error['type'], $fatalErrors)) {
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
                $traceInfo = $this->getFatalStackTraceInfo($trace);
                $this->processFatal($error, $traceInfo);
            }
        };

        // @codingStandardsIgnoreLine
        register_shutdown_function($shutdownFunction);
    }

    /**
     * @param array $stackTrace
     *
     * @return string
     */
    public function getFatalStackTraceInfo(array $stackTrace): string
    {
        $stackTrace = array_reverse($stackTrace);
        $info = '';

        if (count($stackTrace) > 1) {
            foreach ($stackTrace as $key => $trace) {
                $info .= "#{$key} {$trace['file']}({$trace['line']}):";
                $info .= " {$trace['class']}{$trace['type']}{$trace['function']}(";

                if (!empty($trace['args'])) {
                    foreach ($trace['args'] as $argKey => $arg) {
                        $argKey !== 0 && $info .= ',';

                        if (is_object($arg)) {
                            $info .= get_class($arg);
                        } else {
                            $info .= $arg;
                        }
                    }
                }
                $info .= ")\n";
            }
        }

        if ($info === '') {
            $info = 'Unavailable';
        }

        return <<<TRACE
-------------------------------- STACK TRACE INFO --------------------------------
{$info}

TRACE;
    }

    private function processFatal(array $error, string $traceInfo): void
    {
        try {
            $class = 'Fatal Error';

            if (isset($error['message']) && strpos($error['message'], 'Allowed memory size') !== false) {
                $this->writeSystemLogByDirectSql(
                    \M2E\Otto\Model\Log\System::TYPE_FATAL_ERROR,
                    $class,
                    $error['message'],
                    $this->getFatalInfo($error, 'Fatal Error'),
                );

                return;
            }

            $info = $this->getFatalErrorDetailedInfo($error, $traceInfo);

            $this->systemLog(
                \M2E\Otto\Model\Log\System::TYPE_FATAL_ERROR,
                $class,
                $error['message'],
                $info,
            );
            // @codingStandardsIgnoreLine
        } catch (\Throwable $e) {
        }
    }

    /**
     * @param int $type
     * @param string $class
     * @param string $message
     * @param string $description
     *
     * @return void
     * @throws \Exception
     */
    private function writeSystemLogByDirectSql(int $type, string $class, string $message, string $description): void
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));

        $this->resourceConnection->getConnection()->insert(
            $this->systemLogTableName,
            [
                'type' => $type,
                'class' => $class,
                'description' => $message,
                'detailed_description' => $description,
                'create_date' => $date->format('Y-m-d H:i:s'),
            ],
        );
    }

    /**
     * @param array $error
     * @param string $type
     *
     * @return string
     */
    private function getFatalInfo(array $error, string $type): string
    {
        return <<<FATAL
-------------------------------- FATAL ERROR INFO --------------------------------
Type: {$type}
File: {$error['file']}
Line: {$error['line']}
Message: {$error['message']}

FATAL;
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFatalErrorDetailedInfo(array $error, string $traceInfo): string
    {
        $info = $this->getFatalInfo($error, 'Fatal Error');
        $info .= $traceInfo;
        $info .= $this->getAdditionalActionInfo();
        $info .= $this->logHelper->platformInfo();
        $info .= $this->logHelper->moduleInfo();

        return $info;
    }

    public function getUserMessage(\Throwable $exception): string
    {
        return __('Fatal error occurred') . ': "' . $exception->getMessage() . '".';
    }
}
