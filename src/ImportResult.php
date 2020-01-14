<?php
namespace Networkteam\Import;



class ImportResult {

	/**
	 * @var array
	 */
	protected $errors = array();

	/**
	 * @var integer
	 */
	protected $countImported = 0;

	/**
	 * @var integer
	 */
	protected $countUpdated = 0;

	/**
	 * @var integer
	 */
	protected $countDeleted = 0;

	/**
	 * @var integer
	 */
	protected $countProcessed = 0;

	public function addError($error) {
		$this->errors[] = $error;
	}

	/**
	 * @param integer $count
	 */
	public function incCountImported($count = 1) {
		$this->countImported += $count;
	}

	/**
	 * @param integer $count
	 */
	public function incCountUpdated($count = 1) {
		$this->countUpdated += $count;
	}

	/**
	 * @param integer $count
	 */
	public function incCountDeleted($count = 1) {
		$this->countDeleted += $count;
	}

	/**
	 * @return int
	 */
	public function getCountDeleted() {
		return $this->countDeleted;
	}

	/**
	 * @return int
	 */
	public function getCountImported() {
		return $this->countImported;
	}

	/**
	 * @return int
	 */
	public function getCountUpdated() {
		return $this->countUpdated;
	}

	/**
	 * @return int
	 */
	public function getCountProcessed() {
		return $this->countProcessed;
	}

	/**
	 * @return array
	 */
	public function getErrors() {
		return $this->errors;
	}

	/**
	 * Increment processed rows
	 *
	 * @param integer $count
	 */
	public function incCountProcessed($count = 1) {
		$this->countProcessed += $count;
	}
}