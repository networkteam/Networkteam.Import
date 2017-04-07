<?php
namespace Networkteam\Import\DataProvider;

/***************************************************************
 *  (c) 2017 networkteam GmbH - all rights reserved
 ***************************************************************/

use Networkteam\Import\Exception\ConfigurationException;
use Networkteam\Import\Exception\InvalidStateException;

class CsvDataProvider implements DataProviderInterface
{

    const KEY_DELIMITER = 'csv.delimiter';

    const KEY_ENCLOSURE = 'csv.enclosure';

    const KEY_FILENAME = 'csv.filename';

    const KEY_FILE_HANDLE = 'csv.fileHandle';

    const KEY_USE_HEADER_ROW = 'csv.useHeaderRow';

    /**
     * @var array
     */
    protected $options = array(
        self::KEY_DELIMITER => ',',
        self::KEY_ENCLOSURE => '"',
        self::KEY_USE_HEADER_ROW => true
    );

    /**
     * @var resource
     */
    protected $csvFileHandle;

    /**
     * @var array
     */
    protected $currentRow = null;

    /**
     * @var int
     */
    protected $rowNumber = -1;

    /**
     * @var array
     */
    protected $headerRow;

    /**
     * @return string
     * @throws \Networkteam\Import\Exception\ConfigurationException
     */
    protected function getFilename() {
        if (isset($this->options[self::KEY_FILENAME])) {
            return (string)$this->options[self::KEY_FILENAME];
        }

        throw new ConfigurationException(sprintf('Missing option %s for %s', self::KEY_FILENAME, __CLASS__), 1491316171);
    }

    protected function getFileHandle() {
        if (isset($this->options[self::KEY_FILE_HANDLE])) {
            return $this->options[self::KEY_FILE_HANDLE];
        }

        throw new ConfigurationException(sprintf('Missing option %s for %s', self::KEY_FILE_HANDLE, __CLASS__), 1491495926);
    }

    /**
     * @return string
     * @throws ConfigurationException
     */
    protected function getDelimiter() {
        if (isset($this->options[self::KEY_DELIMITER])) {
            return (string)$this->options[self::KEY_DELIMITER];
        }

        throw new ConfigurationException(sprintf('Missing option %s for %s', self::KEY_DELIMITER, __CLASS__), 1491316311);
    }

    /**
     * @return string
     * @throws ConfigurationException
     */
    protected function getEnclosure() {
        if (isset($this->options[self::KEY_ENCLOSURE])) {
            return (string)$this->options[self::KEY_ENCLOSURE];
        }

        throw new ConfigurationException(sprintf('Missing option %s for %s', self::KEY_ENCLOSURE, __CLASS__), 1491316311);
    }

    /**
     * @return bool
     * @throws ConfigurationException
     */
    protected function useHeaderRow() {
        if (isset($this->options[self::KEY_USE_HEADER_ROW])) {
            return (bool)$this->options[self::KEY_USE_HEADER_ROW];
        }

        throw new ConfigurationException(sprintf('Missing option %s for %s', self::KEY_USE_HEADER_ROW, __CLASS__), 1491316529);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->currentRow = fgetcsv($this->csvFileHandle, null, $this->getDelimiter(), $this->getEnclosure());
        $this->rowNumber++;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->rowNumber;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->currentRow !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        fseek($this->csvFileHandle, 0);
        $this->rowNumber = -1;
        $this->initializeRowHeader();
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        if (!$this->useHeaderRow()) {
            return $this->currentRow;
        } else {
            return array_combine($this->headerRow, $this->currentRow);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function open()
    {
        if (is_resource($this->getFileHandle())) {
            $this->csvFileHandle = $this->getFileHandle();
        }
        else {
            if (!file_exists($this->getFilename()) || !is_readable($this->getFilename())) {
                throw new \Exception("Could not open " . $this->getFilename() . " for reading! File does not exist.", 1491290697);
            }

            $this->csvFileHandle = fopen($this->getFilename(), 'r');
        }

        $this->initializeRowHeader();
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if (is_resource($this->csvFileHandle)) {
            fclose($this->csvFileHandle);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        if ($this->rowNumber >= 0) {
            throw new InvalidStateException('Cannot set options on an opened data provider', 1491315796);
        }
        $this->options = array_merge($this->options, $options);
    }

    protected function initializeRowHeader()
    {
        if ($this->useHeaderRow()) {
            $this->next();
            if ($this->valid()) {
                $this->headerRow = $this->currentRow;
            }
        }
        $this->next();
    }

    /**
     * @return array
     */
    public function getFieldNames()
    {
        return $this->headerRow;
    }
}
