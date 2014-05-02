<?php
namespace Networkteam\Import\DataProvider;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

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
	 * {@inheritdoc}
	 */
	public function next() {
		$this->dataProvider->next();
	}

	/**
	 * {@inheritdoc}
	 */
	public function key() {
		return $this->dataProvider->key();
	}

	/**
	 * {@inheritdoc}
	 */
	public function valid() {
		return $this->dataProvider->valid();
	}

	/**
	 * {@inheritdoc}
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