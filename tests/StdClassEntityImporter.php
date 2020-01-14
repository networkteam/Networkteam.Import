<?php
namespace Networkteam\Import\Tests;

use Networkteam\Import\EntityImporter;

class StdClassEntityImporter extends EntityImporter
{

    /**
     * @param object $object
     * @param array $dataHash
     * @param string $propertyName
     * @return mixed
     */
    protected function handleCustomProperty($object, array $dataHash, string $propertyName)
    {

    }

    /**
     * Implement this method and return an Object you want to be persisted
     *
     * @param array $dataHash
     * @return object
     */
    protected function fetchObjectToImport(array $dataHash)
    {
        return new \stdClass();
    }
}