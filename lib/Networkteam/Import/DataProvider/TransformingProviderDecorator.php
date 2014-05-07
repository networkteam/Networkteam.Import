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
	 * @var Object
	 */
	protected $expressionHelper;

	/**
	 * @param array $mapping
	 */
	public function setMapping(array $mapping) {
		$this->mapping = $mapping;
	}

	/**
	 * @param Object $expressionHelper A helper object that will be registered in the expression language under the name "helper"
	 */
	public function setExpressionHelper($expressionHelper) {
		$this->expressionHelper = $expressionHelper;
	}

	/**
	 * {@inheritdoc}
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
				$transformedData[$transformedKey] = $this->getFieldValueByExpression($rawData, $rawDataFieldKey);
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
		if (!array_key_exists($rawFieldIdentifier, $rawData)) {
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
		return preg_match(self::EXPRESSION_REGEX, $rawFieldIdentifier, $matches);
	}

	/**
	 * @param string $rawDataFieldKey
	 * @param array $rawData
	 * @return string
	 */
	protected function getFieldValueByExpression(array $rawData, $rawDataFieldKey) {
		$exp = array();
		preg_match(self::EXPRESSION_REGEX, $rawDataFieldKey, $exp);
		$expression = new \Symfony\Component\ExpressionLanguage\ExpressionLanguage();
		$context = array(
			'row' => $rawData,
			'helper' => $this->expressionHelper !== NULL ? $this->expressionHelper : new TransformerHelper()
		);
		return $expression->evaluate($exp[1], $context);
	}

}