<?php

namespace Ipol\Fivepost\Core\Entity;

/**
 * Class BasicEntity
 * @package Ipol\Fivepost\Core
 * @subpackage Entity
 */
class BasicEntity
{
    protected $fields;

    public function getField($code)
    {
        return (array_key_exists($code, $this->fields)) ? $this->fields[$code] : false;
    }

    public function setField($code, $val)
    {
        $this->fields[$code] = $val;

        return $this;
    }

    public function getFieldList()
    {
        return array_keys($this->fields);
    }
}