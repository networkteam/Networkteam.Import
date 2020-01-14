<?php
namespace Networkteam\Import\DataProvider;

interface DataProviderInterface extends \Iterator
{

    /**
     * @return array The current import item as an associative array (key / value)
     */
    public function current(): array;

    /**
     * @throws \Networkteam\Import\Exception
     */
    public function open(): void;

    /**
     * @throws \Networkteam\Import\Exception
     */
    public function close(): void;

    /**
     * @param array $options
     */
    public function setOptions(array $options): void;

}
