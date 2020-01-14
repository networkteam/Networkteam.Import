<?php
namespace Networkteam\Import\DataProvider;

class PagingProviderDecorator extends BaseProviderDecorator
{

    const KEY_OFFSET = 'provider.offset';

    const KEY_LIMIT = 'provider.limit';

    /**
     * The offset to start from the underlying provider
     *
     * @var int
     */
    protected $offset = 0;

    /**
     * The limit of maximum items to process
     *
     * @var int
     */
    protected $limit = null;

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->dataProvider->current();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        parent::next();
        $this->position++;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return ($this->limit === null || $this->position < $this->limit) && parent::valid();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        parent::rewind();
        $this->skipToOffset();
        $this->position = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function open()
    {
        parent::open();
        $this->skipToOffset();
        $this->position = 0;
    }

    protected function skipToOffset(): void
    {
        for ($i = 0; $i < $this->offset && $this->valid(); $i++) {
            parent::next();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        if (array_key_exists(self::KEY_OFFSET, $options)) {
            $this->offset = $options[self::KEY_OFFSET];
        }
        if (array_key_exists(self::KEY_LIMIT, $options)) {
            $this->limit = $options[self::KEY_LIMIT];
        }
        parent::setOptions($options);
    }

    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    public function setLimit(?int $limit): void
    {
        $this->limit = $limit;
    }

}