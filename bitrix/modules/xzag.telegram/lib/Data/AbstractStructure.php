<?php

namespace Xzag\Telegram\Data;

abstract class AbstractStructure implements DataObjectInterface
{
    /**
     * @param array $data
     * @return static
     */
    public static function make(array $data): DataObjectInterface
    {
        $obj = new static();
        foreach ($data as $key => $value) {
            if (property_exists($obj, $key)) {
                $obj->$key = $value;
            }
        }
        return $obj;
    }

    /**
     * @param array $data
     * @return static[]
     */
    public static function collection(array $data): array
    {
        return array_map(function ($item) {
            return static::make($item);
        }, $data);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $properties = get_object_vars($this);
        return $properties;
    }
}
