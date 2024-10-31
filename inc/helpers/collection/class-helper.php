<?php

namespace Sero\Inc\Helpers\Collection;


trait Helper {

	public static function operatorCompare($value1, $operator, $value2)
	{
	    switch ($operator) {
	        case '<':
	            return $value1 < $value2;
	            break;
	        case '<=':
	            return $value1 <= $value2;
	            break;
	        case '>':
	            return $value1 > $value2;
	            break;
	        case '>=':
	            return $value1 >= $value2;
	            break;
	        case '==':
	            return $value1 == $value2;
	            break;
	        case '!=':
	            return $value1 != $value2;
	            break;
	        default:
	            return false;
	    }
	    return false;
	}
	
}
