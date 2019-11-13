<?php

namespace ClickerVolt;

interface ArraySerializer
{

    /**
     * @return array
     */
    function toArray();

    /**
     * @return $this
     */
    function fromArray($array);

    /**
     * 
     */
    function unsetProp($k);
}

trait ArraySerializerImpl
{

    function toArray()
    {

        $array = [];
        foreach ($this as $k => $v) {
            $array[$k] = $v;
        }
        return $array;
    }

    function fromArray($array)
    {

        if (is_array($array)) {
            foreach ($array as $k => $v) {
                $this->{$k} = $v;
            }
        }
        return $this;
    }

    function unsetProp($k)
    {
        unset($this->{$k});
    }
}
