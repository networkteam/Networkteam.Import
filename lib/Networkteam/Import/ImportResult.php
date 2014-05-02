<?php
namespace Networkteam\Import;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

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

	public function addError($error) {
		$this->errors[] = $error;
	}

	public function incCountImported() {
		$this->countImported++;
	}

	public function incCountUpdated() {
		$this->countUpdated++;
	}

	public function incCountDeleted() {
		$this->countDeleted++;
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
	 * @return array
	 */
	public function getErrors() {
		return $this->errors;
	}
}