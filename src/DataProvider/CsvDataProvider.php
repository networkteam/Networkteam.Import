<?php
namespace Networkteam\Import\DataProvider;

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
    protected $options = [
        self::KEY_DELIMITER => ',',
        self::KEY_ENCLOSURE => '"',
        self::KEY_USE_HEADER_ROW => true
    ];

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

    protected function getFilename(): string
    {
        return (string)$this->getOption(self::KEY_FILENAME);
    }

    /**
     * @return resource a file pointer resource
     * @throws ConfigurationException
     */
    protected function getFileHandle()
    {
        $fileHandle = $this->getOption(self::KEY_FILE_HANDLE);

        if (!is_resource($fileHandle)) {
            throw new ConfigurationException(sprintf('%s option is not of type resource in %s', self::KEY_FILE_HANDLE,
                __METHOD__), 1491988981);
        }

        return $fileHandle;
    }

    protected function getDelimiter(): string
    {
        return (string)$this->getOption(self::KEY_DELIMITER);
    }

    protected function getEnclosure(): string
    {
        return (string)$this->getOption(self::KEY_ENCLOSURE);
    }

    protected function useHeaderRow(): bool
    {
        return (bool)$this->getOption(self::KEY_USE_HEADER_ROW);
    }

    protected function hasOption(string $key): bool
    {
        return isset($this->options[$key]);
    }

    /**
     * @param string $key
     * @return mixed
     * @throws ConfigurationException
     */
    protected function getOption(string $key)
    {
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
    public function current(): array
    {
        if (!$this->useHeaderRow()) {
            return $this->currentRow;
        } else {
            if (count($this->headerRow) !== count($this->currentRow)) {
                $exceptionMessage = sprintf('Current row count does not match header row count (current row: %s <=> header row: %s)',
                    implode(',', $this->currentRow), implode(',', $this->headerRow));
                throw new InvalidStateException($exceptionMessage, 1534926207);
            }
            return array_combine($this->headerRow, $this->currentRow);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function open(): void
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

        $this->initializeRowHeader();
        $this->open = true;
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        if (is_resource($this->csvFileHandle)) {
            fclose($this->csvFileHandle);
        }

        $this->open = false;
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options): void
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

    public function getFieldNames(): array
    {
        return $this->headerRow;
    }
}
