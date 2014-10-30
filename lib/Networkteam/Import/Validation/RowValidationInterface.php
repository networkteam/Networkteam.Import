<?php
namespace Networkteam\Import\Validation;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

interface RowValidationInterface {

	/**
	 * @param array $rowFields
	 * @return boolean
	 */
	public function isValid(array $rowFields);
}