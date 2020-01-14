<?php
namespace Networkteam\Import\Validation;



class NotEmptyRowValidator implements RowValidationInterface {

	/**
	 * @var array
	 */
	protected $configuration = array('ignoreFields' => array());

	/**
	 * @param array $configuration
	 */
	public function setConfiguration($configuration) {
		$this->configuration = $configuration;
	}

	/**
	 * @param array $ignoreFields
	 */
	public function setIgnoreFields(array $ignoreFields) {
		$this->configuration['ignoreFields'] = $ignoreFields;
	}

	/**
	 * The row is considered valid as soon as there is one filled field, that is not ignored
	 *
	 * @param array $rowFields
	 * @return boolean
	 */
	public function isValid(array $rowFields) {
		$ignoreFields = array_flip($this->configuration['ignoreFields']);
		$totalFields = count($rowFields);

		$emptyFields = 0;
		foreach ($rowFields as $colName => $value) {
			if (isset($ignoreFields[$colName])) {
				$totalFields--;
				continue;
			}
			if (trim($value) === '') {
				$emptyFields++;
			}
		}

		return ($emptyFields < $totalFields);
	}
}