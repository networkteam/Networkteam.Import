<?php
namespace Networkteam\Import\DataProvider;

use Doctrine\DBAL\Configuration as DBALConfiguration;
use Doctrine\DBAL\Driver\PDOConnection;
use Doctrine\DBAL\Driver\PDOStatement;
use Doctrine\DBAL\DriverManager;
use Networkteam\Import\Exception\ConfigurationException;

/**
 * Receive data from a database using Doctrine DBAL
 *
 * Rows wil be fetched while iterating over the data provider
 * as associative arrays.
 *
 * Usage:
 *
 * $provider = new DoctrineDbalDataProvider();
 * $provider->setOptions(
 *    [
 *        'providerOptions' => [
 *            'path' => 'sqlite.db'
 *            'driver' => 'pdo_sqlite'
 *        ],
 *        'parameters' => [
 *            'type' => 'goodUser'
 *        ]
 *    ]
 * );
 * $provider->setQuery('SELECT * FROM users WHERE type=:type');
 *
 * foreach ($provider as $user) {
 *    ...
 * }
 */
class DoctrineDbalDataProvider extends AbstractDataProvider
{

    const KEY_PROVIDER_OPTIONS = 'providerOptions';
    const KEY_PARAMETERS = 'parameters';

    /**
     * @var PDOConnection
     */
    protected $connection;

    /**
     * Current executed statement
     *
     * @var PDOStatement
     */
    protected $statement = null;

    /**
     * Current row
     *
     * @var array
     */
    protected $data = null;

    /**
     * @var string
     */
    protected $query;

    /**
     * @var array
     */
    protected $options = [
        self::KEY_PROVIDER_OPTIONS => [],
        self::KEY_PARAMETERS => null,
    ];

    /**
     * @var int
     */
    protected $key;

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        $this->data = $this->statement->fetch(\PDO::FETCH_ASSOC);
        $this->key++;
    }

    /**
     * {@inheritDoc}
     */
    public function key(): int
    {
        return $this->key;
    }

    /**
     * {@inheritDoc}
     */
    public function valid(): bool
    {
        return $this->data !== false;
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        // Execute prepared statement on rewind
        $this->statement->execute($this->options[self::KEY_PARAMETERS] ?? null);

        // Pre-fetch first row to allow check for validity
        $this->key = -1;
        $this->next();
    }

    /**
     * {@inheritDoc}
     */
    public function current(): array
    {
        if ($this->data === null || $this->data === false) {
            return [];
        }

        return $this->data;
    }

    /**
     * @throws \Networkteam\Import\Exception
     * @throws \Doctrine\DBAL\DBALException
     */
    public function open(): void
    {
        $config = new DBALConfiguration();
        $params = $this->options[self::KEY_PROVIDER_OPTIONS];
        $this->connection = DriverManager::getConnection($params, $config);

        if ($this->query == null) {
            throw new ConfigurationException('Please set a query with setQuery() first');
        }

        $this->statement = $this->connection->prepare($this->query);
    }

    /**
     * {@inheritDoc}
     */
    public function close(): void
    {
        $this->statement->closeCursor();
        $this->statement = null;
        $this->connection = null;
    }

    /**
     * Set the query to execute when opening this data provider
     *
     * @param string $query
     */
    public function setQuery(string $query): void
    {
        $this->query = $query;
    }
}