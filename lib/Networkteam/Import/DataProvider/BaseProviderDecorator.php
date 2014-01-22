<?php
namespace Networkteam\Import\DataProvider;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

use Networkteam\Import\DataProvider\DataProviderInterface;

abstract class BaseProviderDecorator implements DataProviderInterface {

	/**
	 * @var DataProviderInterface
	 */
	protected $dataProvider;

	/**
	 * @param DataProviderInterface $dataProvider
	 */
	public function __construct(DataProviderInterface $dataProvider) {
		$this->dataProvider = $dataProvider;
	}

	/**
	 * Move forward to next element
	 *
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 */
	public function next() {
		$this->dataProvider->next();
	}

	/**
	 * Return the key of the current element
	 *
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 */
	public function key() {
		return $this->dataProvider->key();
	}

	/**
	 * Checks if current position is valid
	 *
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 */
	public function valid() {
		return $this->dataProvider->valid();
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 */
	public function rewind() {
		$this->dataProvider->rewind();
	}

	/**
	 * @throws \Networkteam\Import\Exception
	 */
	public function open() {
		$this->dataProvider->open();
	}

	/**
	 * @throws \Networkteam\Import\Exception
	 */
	public function close() {
		$this->dataProvider->close();
	}

	/**
	 * @param array $options
	 * @return mixed|void
	 */
	public function setOptions(array $options) {
		$this->dataProvider->setOptions($options);
	}
}