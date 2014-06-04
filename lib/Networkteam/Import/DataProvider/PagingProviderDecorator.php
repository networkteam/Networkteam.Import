<?php
namespace Networkteam\Import\DataProvider;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

class PagingProviderDecorator extends BaseProviderDecorator {

	const KEY_OFFSET = 'provider.offset';

	const KEY_LIMIT = 'provider.limit';

	/**
	 * The offset to start from the underlying provider
	 *
	 * @var integer
	 */
	protected $offset = 0;

	/**
	 * The limit of maximum items to process
	 *
	 * @var integer
	 */
	protected $limit = NULL;

	/**
	 * @var integer
	 */
	protected $position = 0;

	/**
	 * {@inheritdoc}
	 */
	public function current() {
		return $this->dataProvider->current();
	}

	/**
	 * {@inheritdoc}
	 */
	public function next() {
		parent::next();
		$this->position++;
	}

	/**
	 * {@inheritdoc}
	 */
	public function valid() {
		return ($this->limit === NULL || $this->position < $this->limit) && parent::valid();
	}

	/**
	 * {@inheritdoc}
	 */
	public function rewind() {
		parent::rewind();
		$this->skipToOffset();
		$this->position = 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function open() {
		parent::open();
		$this->skipToOffset();
		$this->position = 0;
	}

	protected function skipToOffset() {
		for ($i = 0; $i < $this->offset && $this->valid(); $i++) {
			parent::next();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function setOptions(array $options) {
		if (array_key_exists(self::KEY_OFFSET,  $options)) {
			$this->offset = $options[self::KEY_OFFSET];
		}
		if (array_key_exists(self::KEY_LIMIT,  $options)) {
			$this->limit = $options[self::KEY_LIMIT];
		}
		parent::setOptions($options);
	}

	/**
	 * @param int $offset
	 */
	public function setOffset($offset) {
		$this->offset = $offset;
	}

	/**
	 * @param int $limit
	 */
	public function setLimit($limit) {
		$this->limit = $limit;
	}

}