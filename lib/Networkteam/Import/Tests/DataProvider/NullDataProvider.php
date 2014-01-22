<?php
namespace Networkteam\Import\Tests\DataProvider;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

use Networkteam\Import\DataProvider\DataProviderInterface;

class NullDataProvider implements DataProviderInterface {

	protected $data = array();

	/**
	 * Return the current element
	 *
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 */
	public function current() {
		return current($this->data);
	}

	/**
	 * Move forward to next element
	 *
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 */
	public function next() {
		next($this->data);
	}

	/**
	 * Return the key of the current element
	 *
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 */
	public function key() {
		return key($this->data);
	}

	/**
	 * Checks if current position is valid
	 *
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 */
	public function valid() {
		return TRUE;
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 */
	public function rewind() {
		reset($this->data);
	}

	/**
	 * @param array $options
	 * @return mixed|void
	 */
	public function setOptions(array $options) {
	}

	/**
	 * @throws \Networkteam\Import\Exception
	 */
	public function open() {
	}

	/**
	 * @throws \Networkteam\Import\Exception
	 */
	public function close() {
	}
}