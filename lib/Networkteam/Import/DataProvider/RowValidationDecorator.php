<?php
namespace Networkteam\Import\DataProvider;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

use Networkteam\Import\Validation\RowValidationInterface;

/**
 * Class RowValidationDecorator
 * Will validate a row according to configured rowValidators. See example for "NotEmptyRowValidator"
 *
 */
class RowValidationDecorator extends BaseProviderDecorator {

	/**
	 * @var RowValidationInterface
	 */
	protected $rowValidator;

	/**
	 * @inheritdoc
	 */
	public function current() {
		return $this->dataProvider->current();
	}

	/**
	 * @return boolean
	 */
	public function valid() {
		do {
			if ($this->dataProvider->valid() === FALSE) {
				return FALSE;
			}

			$currentRowIsValid = $this->rowValidator->isValid($this->dataProvider->current());

			if ($currentRowIsValid === FALSE) {
				$this->dataProvider->next();
			}
		} while ($currentRowIsValid === FALSE);

		return $currentRowIsValid;
	}

	/**
	 * @param $rowValidator
	 */
	public function setValidator($rowValidator) {
		$this->rowValidator = $rowValidator;
	}
}
