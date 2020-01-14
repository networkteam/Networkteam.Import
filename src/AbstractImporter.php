<?php
namespace Networkteam\Import;

use Networkteam\Import\DataProvider\DataProviderInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractImporter implements ImporterInterface
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ImportResult
     */
    protected $importResult;

    /**
     * {@inheritDoc}
     */
    public function import(DataProviderInterface $dataProvider): ImportResult
    {
        $this->importResult = new ImportResult();

        $this->log('Starting import');

        try {
            $dataProvider->open();
        } catch (Exception $exception) {
            $this->handleException($exception);
            return $this->importResult;
        }

        $this->processImportData($dataProvider);

        try {
            $dataProvider->close();

            $this->log('Import finished');
        } catch (Exception $exception) {
            $this->handleException($exception);
        }

        return $this->importResult;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Implement this method to iterate over the data provider and import each row
     * fetched from the data provider.
     *
     * @param DataProviderInterface $dataProvider
     */
    abstract public function processImportData(DataProviderInterface $dataProvider): void;

    /**
     * @param string $message
     * @param string $level
     */
    protected function log($message, $level = \Psr\Log\LogLevel::NOTICE): void
    {
        if ($this->logger !== null) {
            $this->logger->log($level, $message);
        }
    }

    /**
     * @param \Exception $exception
     * @throws \Exception
     */
    protected function handleException(\Exception $exception): void
    {
        $this->importResult->addError($exception->getMessage());

        if ($this->logger !== null) {
            $this->logger->error($exception->getMessage());
        } else {
            throw $exception;
        }
    }
}
