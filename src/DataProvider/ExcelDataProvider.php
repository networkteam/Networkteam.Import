<?php
namespace Networkteam\Import\DataProvider;

use Networkteam\Import\Exception\ConfigurationException;
use Networkteam\Import\Exception\InvalidStateException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use PhpOffice\PhpSpreadsheet\Worksheet\RowIterator;

class ExcelDataProvider implements DataProviderInterface
{

    const KEY_FILENAME = 'provider.filename';

    /**
     * Number of header rows that should be skipped when iterating over data rows
     */
    const KEY_HEADER_OFFSET = 'excel.header_offset';

    /**
     * Position of the header row for the field name map, 0-based index
     */
    const KEY_HEADER_POSITION = 'excel.header_position';

    /**
     * @var Spreadsheet
     */
    protected $workSheet;

    /**
     * @var RowIterator
     */
    protected $iterator;

    /**
     * @var array
     */
    protected $fieldNames;

    /**
     * @var array
     */
    protected $options = [
        self::KEY_HEADER_OFFSET => 1,
        self::KEY_HEADER_POSITION => 0
    ];

    /**
     * @var bool
     */
    protected $open = false;

    /**
     * @param array $options
     * @throws InvalidStateException
     */
    public function setOptions(array $options): void
    {
        if ($this->open) {
            throw new InvalidStateException('Cannot set options on an opened data provider', 1399470312);
        }
        $this->options = array_merge($this->options, $options);
    }

    /**
     * @return array
     * @throws InvalidStateException
     */
    protected function getDataSet(): array
    {
        if ($this->workSheet === null) {
            throw new InvalidStateException('Load data file first by calling open()', 1399470690);
        }

        $dataRow = $this->iterator->current();

        return $this->mapDataRowToCellArray($dataRow);
    }

    /**
     * Read from configured(excel.header_offset) line (defaults to first line) of the sheet
     *
     * @throws ConfigurationException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function extractHeaderFieldNames(): void
    {
        $this->iterator->seek($this->options[self::KEY_HEADER_POSITION] + 1);

        $this->fieldNames = [];

        $fieldNameRow = $this->iterator->current();
        foreach ($fieldNameRow->getCellIterator() as $cell) {
            $this->fieldNames[$cell->getColumn()] = trim(mb_strtolower($cell->getValue(), 'UTF-8'));
        }

        if ($this->fieldNames === []) {
            throw new ConfigurationException('Empty map of field names, please specify a correct KEY_HEADER_POSITION option',
                1399546177);
        }
    }

    public function getFieldNames(): array
    {
        return array_values($this->fieldNames);
    }

    /**
     * {@inheritdoc}
     */
    public function current(): array
    {
        return $this->getDataSet();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->iterator->next();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->iterator->key();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->iterator->key() <= $this->workSheet->getActiveSheet()->getHighestDataRow();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->iterator->rewind();
        $this->moveIteratorBehindHeaderOffset();
    }

    public function open(): void
    {
        $this->open = true;
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($this->getFileName());
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(true);
        $spreadsheet = $reader->load($this->getFileName());

        $this->initializeIteratorAndFieldNames($spreadsheet);
    }

    public function close(): void
    {
        $this->open = false;
        $this->workSheet = null;
        $this->iterator = null;
    }

    protected function initializeIteratorAndFieldNames(Spreadsheet $workSheet): void
    {
        $this->workSheet = $workSheet;
        $this->iterator = $workSheet->getActiveSheet()->getRowIterator();
        $this->extractHeaderFieldNames();
        $this->moveIteratorBehindHeaderOffset();
    }

    public function setFileName(string $filename): void
    {
        $this->options[self::KEY_FILENAME] = $filename;
    }

    /**
     * @return string
     * @throws ConfigurationException
     */
    protected function getFileName(): string
    {
        if (isset($this->options[self::KEY_FILENAME])) {
            return $this->options[self::KEY_FILENAME];
        }
        if (isset($this->options['excel.source_file'])) {
            return $this->options['excel.source_file'];
        }
        throw new ConfigurationException('Missing option excel.source_file for ExcelDataProvider', 1399470636);
    }

    protected function mapDataRowToCellArray(Row $dataRow): array
    {
        $dataArray = $this->getEmptyDataArray();
        foreach ($dataRow->getCellIterator() as $cell) {
            $cellColumn = $cell->getColumn();
            if (array_key_exists($cellColumn, $this->fieldNames)) {
                $dataArray[$this->fieldNames[$cellColumn]] = $cell->getValue();
            }
        }
        return $dataArray;
    }

    protected function getEmptyDataArray(): array
    {
        $dataArray = [];
        foreach ($this->fieldNames as $fieldNameKey) {
            $dataArray[$fieldNameKey] = null;
        }

        return $dataArray;
    }

    protected function moveIteratorBehindHeaderOffset(): void
    {
        if ($this->iterator->key() <= $this->options[self::KEY_HEADER_OFFSET]) {
            $this->iterator->seek($this->options[self::KEY_HEADER_OFFSET] + 1);
        }
    }
}
