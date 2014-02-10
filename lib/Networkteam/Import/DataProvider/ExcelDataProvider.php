<?php

namespace Networkteam\Import\DataProvider;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

class ExcelDataProvider implements \Networkteam\Import\DataProvider\DataProviderInterface {

	const KEY_SOURCE_FILE = 'excel.source_file';

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
	 * @param array $options
	 * @return mixed|void
	 */
	public function setOptions(array $options) {
		$this->options = array_merge($this->options, $options);
	}

	/**
	 * @return \PHPExcel_Worksheet_Row
	 */
	public function getDataSet() {
		if ($this->workSheet === NULL) {
			throw new \InvalidArgumentException('Load dataFile first by calling open()');
		}

		$dataRow = $this->iterator->current();
		if ($dataRow->getRowIndex() <= $this->options[self::KEY_HEADER_OFFSET]) {
			$this->iterator->seek($this->options[self::KEY_HEADER_OFFSET] + 1);
			$dataRow = $this->iterator->current();
		}

		return $this->mapDataRowToCellArray($dataRow);
	}

	/**
	 * Read the headlines from the first line of the sheet
	 */
	protected function extractHeaderFieldNames() {
		$fieldNameRow = $this->iterator->current();
		/** @var $cell \PHPExcel_Cell */
		foreach ($fieldNameRow->getCellIterator() as $cell) {
			$this->fieldNames[$cell->getColumn()] = strtolower($cell->getValue());
		}
	}

	/**
	 * @return array
	 */
	public function getFieldNames() {
		return $this->fieldNames;
	}

	/**
	 * Return the current element
	 *
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 */
	public function current() {
		return $this->getDataSet();
	}

	/**
	 * Move forward to next element
	 *
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 */
	public function next() {
		$this->iterator->next();
	}

	/**
	 * Return the key of the current element
	 *
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 */
	public function key() {
		return $this->iterator->key();
	}

	/**
	 * Checks if current position is valid
	 *
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 */
	public function valid() {
		return $this->iterator->key() <= $this->workSheet->getActiveSheet()->getHighestDataRow();
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 */
	public function rewind() {
		$this->iterator->rewind();
	}

	/**
	 * @throws \Networkteam\Import\Exception
	 */
	public function open() {
		$worksheet = \PHPExcel_IOFactory::load($this->getFileName());
		$this->initializeIteratorAndFieldNames($worksheet);
	}

	/**
	 * @throws \Networkteam\Import\Exception
	 */
	public function close() {
		// TODO: Implement close() method.
	}

	/**
	 * @param \PHPExcel $workSheet
	 */
	protected function initializeIteratorAndFieldNames(\PHPExcel $workSheet) {
		$this->workSheet = $workSheet;
		$this->iterator = $workSheet->getActiveSheet()->getRowIterator();
		$this->extractHeaderFieldNames();
	}

	/**
	 * @param string $filename
	 */
	public function setFileName($filename) {
		$this->options[self::KEY_SOURCE_FILE] = $filename;
	}

	/**
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	protected function getFileName() {
		if(isset($this->options[self::KEY_SOURCE_FILE])) {
			return $this->options[self::KEY_SOURCE_FILE];
		}
		throw new \InvalidArgumentException('Missing options excel.source_file for ExcelDataProvider');
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
}
