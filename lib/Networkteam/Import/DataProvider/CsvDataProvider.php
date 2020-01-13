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

    const KEY_FILE_ENCODING = 'csv.fileEncoding';

    const KEY_FILE_ENCODING_VALIDATION_ENABLED = 'csv.fileEncodingValidationEnabled';

    const KEY_USE_HEADER_ROW = 'csv.useHeaderRow';

    /**
     * @var array
     */
    protected $options = array(
        self::KEY_DELIMITER => ',',
        self::KEY_ENCLOSURE => '"',
        self::KEY_USE_HEADER_ROW => true,
        self::KEY_FILE_ENCODING => 'UTF-8',
        self::KEY_FILE_ENCODING_VALIDATION_ENABLED => false
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
     * @var boolean
     */
    protected $open = false;

    /**
     * @return string
     */
    protected function getFilename() {
        return (string)$this->getOption(self::KEY_FILENAME);
    }

    /**
     * @return resource a file pointer resource
     * @throws ConfigurationException
     */
    protected function getFileHandle() {
        $fileHandle = $this->getOption(self::KEY_FILE_HANDLE);

        if (!is_resource($fileHandle)) {
            throw new ConfigurationException(sprintf('%s option is not of type resource in %s', self::KEY_FILE_HANDLE, __METHOD__), 1491988981);
        }

        return $fileHandle;
    }

    /**
     * @return string
     */
    protected function getDelimiter() {
        return (string)$this->getOption(self::KEY_DELIMITER);
    }

    /**
     * @return string
     */
    protected function getEnclosure() {
        return (string)$this->getOption(self::KEY_ENCLOSURE);
    }

    /**
     * @return bool
     */
    protected function useHeaderRow() {
        return (bool)$this->getOption(self::KEY_USE_HEADER_ROW);
    }

    /**
     * @return string
     */
    protected function getFileEncoding() {
        return (string)$this->getOption(self::KEY_FILE_ENCODING);
    }

    /**
     * @return bool
     */
    protected function isFileEncodingValidationEnabled() {
        return (bool)$this->getOption(self::KEY_FILE_ENCODING_VALIDATION_ENABLED);
    }

    /**
     * @param string $key
     * @return bool
     */
    protected function hasOption($key) {
        return isset($this->options[$key]);
    }

    /**
     * @param string $key
     * @return mixed
     * @throws ConfigurationException
     */
    protected function getOption($key) {
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }

        throw new ConfigurationException(sprintf('Missing option %s in %s', $key, __CLASS__), 1491316529);
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
            if (count($this->headerRow) !== count($this->currentRow)) {
                $exceptionMessage = sprintf('Current row count does not match header row count (current row: %s <=> header row: %s)', implode(',', $this->currentRow), implode(',', $this->headerRow));
                throw new InvalidStateException($exceptionMessage, 1534926207);
            }
            return array_combine($this->headerRow, $this->currentRow);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function open()
    {
        $this->setCsvFileHandle();

        if ($this->isFileEncodingValidationEnabled()) {
            $this->validateFileEncoding();
        }

        $this->initializeRowHeader();
        $this->open = true;
    }


    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if (is_resource($this->csvFileHandle)) {
            fclose($this->csvFileHandle);
        }

        $this->open = false;
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        if ($this->open) {
            throw new InvalidStateException('Cannot set options on an opened data provider', 1491315796);
        }
        $this->options = array_merge($this->options, $options);
    }

    protected function initializeRowHeader(): void
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

    /**
     * @throws \Exception
     */
    protected function setCsvFileHandle(): void
    {
        if ($this->hasOption(self::KEY_FILE_HANDLE)) {
            $this->csvFileHandle = $this->getFileHandle();
        } else {
            if (!file_exists($this->getFilename()) || !is_readable($this->getFilename())) {
                throw new \Exception("Could not open " . $this->getFilename() . " for reading! File does not exist.",
                    1491290697);
            }

            $this->csvFileHandle = fopen($this->getFilename(), 'r');
        }
    }

    /**
     * @throws \Exception
     */
    protected function validateFileEncoding(): void
    {
        try {
            $content = stream_get_contents($this->getFileHandle(), 1048576);
            if (!mb_check_encoding($content, $this->getFileEncoding())) {
                throw new \Exception(sprintf('File content does not follow %s encoding.', $this->getFileEncoding()), 1578925169);
            }
        } catch (ConfigurationException $e) {
            throw new \Exception(sprintf('Could not validate file encoding due to configuration exception: %s', $e->getMessage()), 1578926968);
        }

        $this->rewind();
    }
}
