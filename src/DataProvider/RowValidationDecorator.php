<?php
namespace Networkteam\Import\DataProvider;

use Networkteam\Import\Validation\RowValidationInterface;

/**
 * Provider decorator for validating rows
 *
 * Will validate a row according to configured row validators. See example for "NotEmptyRowValidator".
 */
class RowValidationDecorator extends BaseProviderDecorator
{

    const OPTION_SKIP_ON_EXCEPTION = 'row_validator.skip_on_exception';

    /**
     * @var array
     */
    protected $options = [
        self::OPTION_SKIP_ON_EXCEPTION => true
    ];

    /**
     * @var RowValidationInterface
     */
    protected $rowValidator;

    /**
     * {@inheritdoc}
     */
    public function current(): array
    {
        return $this->dataProvider->current();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function valid()
    {
        do {
            if ($this->dataProvider->valid() === false) {
                return false;
            }

            try {
                $currentRow = $this->dataProvider->current();
                $currentRowIsValid = $this->rowValidator->isValid($currentRow);
            } catch (\Exception $e) {
                if ($this->options[self::OPTION_SKIP_ON_EXCEPTION] === true) {
                    $currentRowIsValid = false;
                } else {
                    throw $e;
                }
            }

            if ($currentRowIsValid === false) {
                $this->dataProvider->next();
            }
        } while ($currentRowIsValid === false);

        return $currentRowIsValid;
    }

    public function setValidator(RowValidationInterface $rowValidator): void
    {
        $this->rowValidator = $rowValidator;
    }

    public function setOptions(array $options): void
    {
        $this->options = array_merge($this->options, $options);
        parent::setOptions($options); // TODO: Change the autogenerated stub
    }
}
