<?php
namespace Networkteam\Import;

interface ImporterInterface
{

    /**
     * Import data from the data provider by processing each row
     *
     * @param \Networkteam\Import\DataProvider\DataProviderInterface $dataProvider The data provider to use as the data source of the importer
     * @return ImportResult
     * @throws \Exception
     */
    public function import(\Networkteam\Import\DataProvider\DataProviderInterface $dataProvider): ImportResult;
}