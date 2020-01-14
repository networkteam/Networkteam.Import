<?php
namespace Networkteam\Import\DataProvider;

abstract class BaseProviderDecorator implements DataProviderInterface
{

    /**
     * @var DataProviderInterface
     */
    protected $dataProvider;

    /**
     * @param DataProviderInterface $dataProvider
     */
    public function __construct(DataProviderInterface $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->dataProvider->next();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->dataProvider->key();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->dataProvider->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->dataProvider->rewind();
    }

    /**
     * {@inheritDoc}
     */
    public function open(): void
    {
        $this->dataProvider->open();
    }

    /**
     * {@inheritDoc}
     */
    public function close(): void
    {
        $this->dataProvider->close();
    }

    /**
     * {@inheritDoc}
     */
    public function setOptions(array $options): void
    {
        $this->dataProvider->setOptions($options);
    }
}