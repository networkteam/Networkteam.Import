<?php
namespace Networkteam\Import\DataProvider;

use Networkteam\Import\Exception\ConfigurationException;

/**
 * Receive data from a database using doctrine dbal
 *
 * Usage:
 * $provider = eDoctrineDbalProvider();
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
 * foreach($provider as $user) {
 *    ...
 * }
 */
class DoctrineDbalDataProvider extends AbstractDataProvider
{

    /**
     * @var \Doctrine\DBAL\Driver\PDOConnection
     */
    protected $connection;

    /**
     * @var \Doctrine\DBAL\Driver\PDOStatement
     */
    protected $statement;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var string
     */
    protected $query;

    /**
     * @var array
     */
    protected $options = [
        'providerOptions' => [],
        'user' => null,
        'password' => null,
        'options' => null
    ];

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        next($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        return current($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        reset($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * @throws \Networkteam\Import\Exception
     * @throws \Doctrine\DBAL\DBALException
     */
    public function open()
    {
        $config = new \Doctrine\DBAL\Configuration();
        $params = $this->options['providerOptions'];
        $this->connection = \Doctrine\DBAL\DriverManager::getConnection($params, $config);

        if ($this->query == null) {
            throw new ConfigurationException('Please set a query with setQuery() first.');
        }

        $this->statement = $this->connection->prepare($this->query);
        $this->statement->execute(isset($this->options['parameters']) ? $this->options['parameters'] : null);
        $this->data = $this->statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        $this->connection = null;
    }
}