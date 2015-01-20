<?php

namespace JVCrud\Filter;

class Url
{
    public function verifyUrlQueryFilter($paramsQuery)
    {
        $fromQuery = null;
        
        if (is_array($paramsQuery) && count($paramsQuery))
        {
            $fromQuery = '?'.http_build_query($paramsQuery); // http://www.php.net/manual/pt_BR/function.http-build-query.php
        }
        
        return $fromQuery;
    }

}
