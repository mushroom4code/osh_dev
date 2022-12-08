<?php

namespace Xzag\Telegram\Data;

/**
 * Interface DataObjectInterface
 * @package Xzag\Telegram\Data
 */
interface DataObjectInterface
{
    /**
     * @param array $data
     * @return DataObjectInterface
     */
    public static function make(array $data): DataObjectInterface;

    /**
     * @return array
     */
    public function toArray(): array;
}
