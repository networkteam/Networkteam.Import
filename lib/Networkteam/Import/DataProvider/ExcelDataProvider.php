<?php

namespace Networkteam\Import\DataProvider;
use InvalidArgumentException;
use Networkteam\Import\Exception\ConfigurationException;
use Networkteam\Import\Exception\InvalidStateException;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

class ExcelDataProvider implements \Networkteam\Import\DataProvider\DataProviderInterface {

	const KEY_FILENAME = 'provider.filename';

	const KEY_HEADER_OFFSET = 'excel.header_offset';

	/**
	 * @var \PHPExcel
	 */
	protected $workSheet;

	/**
	 * @var \PHPExcel_Worksheet_RowIterator
	 */
	protected $iterator;

	/**
	 * @var array
	 */
	protected $fieldNames;

	/**
	 * @var array
	 */
	protected $options = array(self::KEY_HEADER_OFFSET => 1);

	/**
	 * @var boolean
	 */
	protected $open = FALSE;

	/**
	 * @param array $options
	 * @throws \Networkteam\Import\Exception\InvalidStateException
	 */
	public function setOptions(array $options) {
		if ($this->open) {
			throw new InvalidStateException('Cannot set options on an opened data provider', 1399470312);
		}
		$this->options = array_merge($this->options, $options);
	}

	/**
	 * @return array
	 * @throws \Networkteam\Import\Exception
	 */
	protected function getDataSet() {
		if ($this->workSheet === NULL) {
			throw new InvalidStateException('Load data file first by calling open()', 1399470690);
		}

		$dataRow = $this->iterator->current();

		return $this->mapDataRowToCellArray($dataRow);
	}

	/**
	 * Read the headlines from the first line of the sheet
	 */
	protected function extractHeaderFieldNames() {
		$fieldNameRow = $this->iterator->current();
		/** @var $cell \PHPExcel_Cell */
		foreach ($fieldNameRow->getCellIterator() as $cell) {
			$this->fieldNames[$cell->getColumn()] = mb_strtolower($cell->getValue(), 'UTF-8');
		}
	}

	/**
	 * @return array
	 */
	public function getFieldNames() {
		return array_values($this->fieldNames);
	}

	/**
	 * {@inheritdoc}
	 */
	public function current() {
		return $this->getDataSet();
	}

	/**
	 * {@inheritdoc}
	 */
	public function next() {
		$this->iterator->next();
	}

	/**
	 * {@inheritdoc}
	 */
	public function key() {
		return $this->iterator->key();
	}

	/**
	 * {@inheritdoc}
	 */
	public function valid() {
		return $this->iterator->key() <= $this->workSheet->getActiveSheet()->getHighestDataRow();
	}

	/**
	 * {@inheritdoc}
	 */
	public function rewind() {
		$this->iterator->rewind();
		$this->moveIteratorBehindHeaderOffset();
	}

	public function open() {
		$this->open = TRUE;
		$worksheet = \PHPExcel_IOFactory::load($this->getFileName());
		$this->initializeIteratorAndFieldNames($worksheet);
	}

	public function close() {
		$this->open = FALSE;
		$this->workSheet = NULL;
		$this->iterator = NULL;
	}

	/**
	 * @param \PHPExcel $workSheet
	 */
	protected function initializeIteratorAndFieldNames(\PHPExcel $workSheet) {
		$this->workSheet = $workSheet;
		$this->iterator = $workSheet->getActiveSheet()->getRowIterator();
		$this->extractHeaderFieldNames();
		$this->moveIteratorBehindHeaderOffset();
	}

	/**
	 * @param string $filename
	 */
	public function setFileName($filename) {
		$this->options[self::KEY_FILENAME] = $filename;
	}

	/**
	 * @return mixed
	 * @throws \Networkteam\Import\Exception\ConfigurationException
	 */
	protected function getFileName() {
		if (isset($this->options[self::KEY_FILENAME])) {
			return $this->options[self::KEY_FILENAME];
		}
		if (isset($this->options['excel.source_file'])) {
			return $this->options['excel.source_file'];
		}
		throw new ConfigurationException('Missing option excel.source_file for ExcelDataProvider', 1399470636);
	}

	/**
	 * @param \PHPExcel_Worksheet_Row $dataRow
	 * @return array
	 */
	protected function mapDataRowToCellArray($dataRow) {
		$dataArray = $this->getEmptyDataArray();
		/** @var $cell \PHPExcel_Cell */
		foreach ($dataRow->getCellIterator() as $cell) {
			$dataArray[$this->fieldNames[$cell->getColumn()]] = $cell->getValue();
		}
		return $dataArray;
	}

	/**
	 * @return array
	 */
	protected function getEmptyDataArray() {
		$dataArray = array();
		foreach ($this->fieldNames as $fieldNameKey) {
			$dataArray[$fieldNameKey] = NULL;
		}

		return $dataArray;
	}

	protected function moveIteratorBehindHeaderOffset() {
		if ($this->iterator->key() <= $this->options[self::KEY_HEADER_OFFSET]) {
			$this->iterator->seek($this->options[self::KEY_HEADER_OFFSET] + 1);
		}
	}
}
