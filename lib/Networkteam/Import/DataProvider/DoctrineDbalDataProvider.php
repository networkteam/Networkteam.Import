<?php
namespace Networkteam\Import\DataProvider;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

/**
 * Receive data from a database using doctrine dbal
 *
 * Usage:
 * $provider = eDoctrineDbalProvider();
 * $provider->setOptions(
 * 	array(
 * 		'providerOptions' => array(
 * 			'path' => 'sqlite.db'
 * 			'driver' => 'pdo_sqlite'
 * 		),
 * 		'parameters' => array(
 *			'type' => 'goodUser'
 * 		)
 * 	)
 * );
 * $provider->setQuery('SELECT * FROM users WHERE type=:type');
 *
 * foreach($provider as $user) {
 * 	...
 * }
 */
class DoctrineDbalDataProvider extends AbstractDataProvider {

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
	protected $options = array(
		'providerOptions' => array(),
		'user' => NULL,
		'password' => NULL,
		'options' => NULL
	);

	/**
	 * @inheritdoc
	 */
	public function next() {
		next($this->data);
	}

	/**
	 * @inheritdoc
	 */
	public function key() {
		return key($this->data);
	}

	/**
	 * @inheritdoc
	 */
	public function valid() {
		return current($this->data);
	}

	/**
	 * @inheritdoc
	 */
	public function rewind() {
		reset($this->data);
	}

	/**
	 * @return array
	 */
	public function current() {
		return current($this->data);
	}

	/**
	 * @throws \Networkteam\Import\Exception
	 */
	public function open() {
		$config = new \Doctrine\DBAL\Configuration();
		$params = $this->options['providerOptions'];
		$this->connection = \Doctrine\DBAL\DriverManager::getConnection($params, $config);

		if($this->query == NULL) {
			throw new \Networkteam\Import\Exception\ConfigurationException('Please set a query with setQuery() first.');
		}

		$this->statement = $this->connection->prepare($this->query);
		$this->statement->execute(isset($this->options['parameters']) ? $this->options['parameters']: NULL);
		$this->data = $this->statement->fetchAll(\PDO::FETCH_ASSOC);
	}

	/**
	 * @throws \Networkteam\Import\Exception
	 */
	public function close() {
		$this->connection = NULL;
	}
}