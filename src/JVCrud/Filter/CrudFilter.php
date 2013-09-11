<?php

namespace JVCrud\Filter;

class CrudFilter
{
    public function extract(array $valoresFilter, array $esquemaFilter)
    {
        // define o array de resultado
        //$result = "WHERE 1=1 ";
        $result = array();
        
        // Definindo variáveis
        $totalValores = count($valoresFilter);
        
        // Validações
        if (count($esquemaFilter) == 2) 
        {
            if (!isset($esquemaFilter['options']) && !is_array($esquemaFilter['options']))
            {
                throw new \InvalidArgumentException('Faltou passar os parâmetros array options');
            }
            $totalOptions = count($esquemaFilter['options']);
            
            if (!isset($esquemaFilter['values']) && !is_array($esquemaFilter['values']))
            {
                throw new \InvalidArgumentException('Faltou passar os parâmetros array values');
            }
            $totalValues = count($esquemaFilter['values']);
        } 
        else 
        {
            throw new \InvalidArgumentException('Você precisa passar os parâmetros options e values');
        }
        
        if ($totalValues == $totalOptions)
        {
            foreach ($esquemaFilter['values'] as $key => $value)
            {
                if (isset($valoresFilter[$value]) && strlen($valoresFilter[$value])) {
                    switch ($esquemaFilter['options'][$key])
                    {
                        case "int" : $result[$key] = (int) $valoresFilter[$value];
                            break;
                        case "boolean" : $result[$key] = ($valoresFilter[$value] > 0) ? $valoresFilter[$value] : '0';
                            break;
                        case "string" : $result["$key LIKE ?"] = "%" . $valoresFilter[$value] . "%";
                            break;
                    }
                }
            }
        }
        
        
        return $result;
        
        if (count($result)) {
            //$return = " WHERE 1=1";
            //$return .= implode(" AND ", $result);
            
            //return $return;
            return $result;
        } else {
            return array();
        }
    }
}