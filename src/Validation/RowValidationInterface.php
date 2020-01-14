<?php
namespace Networkteam\Import\Validation;

interface RowValidationInterface
{

    /**
     * @param array $rowFields
     * @return boolean
     */
    public function isValid(array $rowFields);
}