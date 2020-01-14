<?php
namespace Networkteam\Import\Tests\DataProvider;

use Networkteam\Import\DataProvider\DataProviderInterface;

class NullDataProvider implements DataProviderInterface
{

    protected $data = array();

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        next($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        reset($this->data);
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
    }

    /**
     * @throws \Networkteam\Import\Exception
     */
    public function open()
    {
    }

    /**
     * @throws \Networkteam\Import\Exception
     */
    public function close()
    {
    }
}