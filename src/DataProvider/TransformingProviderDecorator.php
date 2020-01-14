<?php
namespace Networkteam\Import\DataProvider;

use Networkteam\Import\Exception\ConfigurationException;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class TransformingProviderDecorator extends BaseProviderDecorator
{

    const EXPRESSION_REGEX = '/\${([^}]*)}/';

    /**
     * @var array
     */
    protected $mapping = [];

    /**
     * @var object
     */
    protected $expressionHelper;

    /**
     * @var ExpressionLanguage
     */
    protected $el;

    public function __construct(DataProviderInterface $dataProvider)
    {
        parent::__construct($dataProvider);
        $this->el = new ExpressionLanguage();
    }

    public function setMapping(array $mapping): void
    {
        $this->mapping = $mapping;
    }

    /**
     * @param object $expressionHelper A helper object that will be registered in the expression language under the name "helper"
     */
    public function setExpressionHelper($expressionHelper): void
    {
        $this->expressionHelper = $expressionHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function current(): array
    {
        $rawData = $this->dataProvider->current();
        return $this->transformData($rawData);
    }

    /**
     * @param array $rawData
     * @return array
     * @throws ConfigurationException
     * @throws \Networkteam\Import\Exception\InvalidStateException
     * @throws \Networkteam\Import\Exception
     */
    protected function transformData(array $rawData): array
    {
        $transformedData = [];
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
     * @param string $rawFieldIdentifier
     * @return mixed
     * @throws ConfigurationException
     */
    protected function getFieldDataByName(array $rawData, string $rawFieldIdentifier)
    {
        if (!array_key_exists($rawFieldIdentifier, $rawData)) {
            $exceptionMessage = sprintf('The key "%s" was not found in the list of keys: %s', $rawFieldIdentifier,
                implode(', ', array_keys($rawData)));
            throw new ConfigurationException($exceptionMessage, 1389792450);
        }

        return $rawData[$rawFieldIdentifier];
    }

    protected function keyIsExpression(string $rawFieldIdentifier): bool
    {
        $matches = [];
        return preg_match(self::EXPRESSION_REGEX, $rawFieldIdentifier, $matches) === 1;
    }

    /**
     * @param array $rawData
     * @param string $rawDataFieldKey
     * @return mixed
     * @throws \Networkteam\Import\Exception
     */
    protected function getFieldValueByExpression(array $rawData, string $rawDataFieldKey)
    {
        $exp = [];
        if (preg_match(self::EXPRESSION_REGEX, $rawDataFieldKey, $exp) !== 1) {
            throw new \Networkteam\Import\Exception('Expected field key to be an expression', 1578997540);
        }
        $context = [
            'row' => $rawData,
            'helper' => $this->expressionHelper !== null ? $this->expressionHelper : new TransformerHelper()
        ];
        return $this->el->evaluate($exp[1], $context);
    }

}