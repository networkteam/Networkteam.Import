<?php
namespace Networkteam\Import;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

use Networkteam\Import\DataProvider\DataProviderInterface;

abstract class AbstractImporter {

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * @var \Networkteam\Import\DataProvider\DataProviderInterface
	 */
	protected $dataProvider;

	/**
	 * @var ImportResult
	 */
	protected $importResult;

	/**
	 * @param DataProviderInterface $dataProvider
	 */
	public function __construct(DataProviderInterface $dataProvider) {
		$this->dataProvider = $dataProvider;
		$this->importResult = new ImportResult();
	}

	/**
	 * @return ImportResult
	 */
	public function import() {
		$this->log('Starting import');

		try {
			$this->dataProvider->open();
		} catch (Exception $exception) {
			$this->handleException($exception);
			return $this->importResult;
		}

		$this->processImportData();

		try {
			$this->dataProvider->close();
		} catch (Exception $exception) {
			$this->handleException($exception);
		}

		$this->log('Import finished');

		return $this->importResult;
	}

	/**
	 * @param \Psr\Log\LoggerInterface $logger
	 */
	public function setLogger(\Psr\Log\LoggerInterface $logger) {
		$this->logger = $logger;
	}

	/**
	 * Implement this method to iterate over $this->dataProvider and import each row
	 * fetched from the data provider
	 */
	abstract public function processImportData();

	/**
	 * @param string $message
	 * @param string $level
	 */
	protected function log($message, $level = \Psr\Log\LogLevel::NOTICE) {
		if ($this->logger !== NULL) {
			$this->logger->log($level, $message);
		}
	}

	/**
	 * @param \Exception $exception
	 * @throws \Exception
	 */
	protected function handleException(\Exception $exception) {
		$this->importResult->addError($exception->getMessage());

		if ($this->logger !== NULL) {
			$this->logger->error($exception->getMessage());
		} else {
			throw $exception;
		}
	}
}
