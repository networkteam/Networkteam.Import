<?php
namespace Networkteam\Import\DataProvider;

abstract class AbstractDataProvider implements DataProviderInterface
{

    /**
     * @var array
     */
    protected $options;

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }
}