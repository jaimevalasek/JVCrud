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
            foreach ($paramsQuery as $key => $value)
            {
                $fromQuery .= "$key=$value&";
            }
            
            $fromQuery = substr($fromQuery, 0, -1);
        }
        
        return $fromQuery;
    }
}