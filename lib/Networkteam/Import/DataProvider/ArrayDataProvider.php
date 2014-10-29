<?php
namespace Networkteam\Import\DataProvider;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

class ArrayDataProvider implements DataProviderInterface {

	/**
	 * @var \Iterator
	 */
	protected $iterator;

	public function __construct(array $array) {
		$this->iterator = new \ArrayIterator($array);
	}

	/**
	 * {@inheritdoc}
	 */
	public function next() {
		$this->iterator->next();
	}

	/**
	 * {@inheritdoc}
	 */
	public function key() {
		return $this->iterator->key();
	}

	/**
	 * {@inheritdoc}
	 */
	public function valid() {
		return $this->iterator->valid();
	}

	/**
	 * {@inheritdoc}
	 */
	public function rewind() {
		$this->iterator->rewind();
	}

	/**
	 * {@inheritdoc}
	 */
	public function current() {
		return $this->iterator->current();
	}

	/**
	 * {@inheritdoc}
	 */
	public function open() {}

	/**
	 * {@inheritdoc}
	 */
	public function close() {}

	/**
	 * {@inheritdoc}
	 */
	public function setOptions(array $options) {}
}
