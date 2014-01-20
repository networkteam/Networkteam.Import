<?php
namespace Networkteam\Import\DataProvider;
/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

class TransformingProviderDecorator extends BaseProviderDecorator {

	const EXPRESSION_REGEX = '/\${([^}]*)}/';

	/**
	 * @var array
	 */
	protected $mapping = array();

	/**
	 * @param array $mapping
	 */
	public function setMapping(array $mapping) {
		$this->mapping = $mapping;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the current element
	 *
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 */
	public function current() {
		$rawData = $this->dataProvider->current();
		$transformedData = $this->transformData($rawData);

		return $transformedData;
	}

	/**
	 * @param array $rawData
	 * @return array
	 */
	protected function transformData(array $rawData) {
		$transformedData = array();
		foreach ($this->mapping as $transformedKey => $rawDataFieldKey) {
			if ($this->keyIsExpression($rawDataFieldKey)) {
				$transformedData[$transformedKey] = $this->getFieldValueByExpression($rawDataFieldKey, $rawData);
			} else {
				$transformedData[$transformedKey] = $this->getFieldDataByName($rawData, $rawDataFieldKey);
			}
		}
		return $transformedData;
	}

	/**
	 * @param array $rawData
	 * @param $rawFieldIdentifier
	 * @return array
	 * @throws \Networkteam\Import\Exception\ConfigurationException
	 */
	protected function getFieldDataByName(array $rawData, $rawFieldIdentifier) {
		if (!isset($rawData[$rawFieldIdentifier])) {
			$exceptionMessage = sprintf('The key "%s" was not found in the list of keys: %s', $rawFieldIdentifier, implode(', ', array_keys($rawData)));
			throw new \Networkteam\Import\Exception\ConfigurationException($exceptionMessage, 1389792450);
		}

		return $rawData[$rawFieldIdentifier];
	}

	/**
	 * @param string $rawFieldIdentifier
	 * @return integer
	 */
	protected function keyIsExpression($rawFieldIdentifier) {
		$matches = array();
		$result = preg_match(self::EXPRESSION_REGEX, $rawFieldIdentifier, $matches);
		return $result;
	}

	/**
	 * @param string $rawDataFieldKey
	 * @param array $rawData
	 * @return string
	 */
	protected function getFieldValueByExpression($rawDataFieldKey, array $rawData) {
		$exp = array();
		preg_match(self::EXPRESSION_REGEX, $rawDataFieldKey, $exp);
		$expression = new \Symfony\Component\ExpressionLanguage\ExpressionLanguage();
		return $expression->evaluate($exp[1], array(
				'row' => $rawData
			)
		);
	}
}