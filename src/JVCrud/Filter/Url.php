<?php

namespace JVCrud\Filter;

class Url
{
    public function verifyUrlQueryFilter($paramsQuery)
    {
        $fromQuery = null;
        
        if (is_array($paramsQuery) && count($paramsQuery))
        {
            $fromQuery = '?';
            $line = array_map(array($this,'map'),array_keys($paramsQuery),array_value($paramsQuery));
            $fromQuery .= implode('&',$line);
        }
        
        return $fromQuery;
    }
    public function map($k,$v){
        return "{$k}={$v}";
    }
}
