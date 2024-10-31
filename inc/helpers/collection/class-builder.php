<?php
/**
 * The Array Builder.
 *
 * @since      1.0.0
 * @package    Sero
 * @subpackage Sero\Inc\Helpers\Collection
 * @author     Sero <laxusgooee@gmail.com>
 */

namespace Sero\Inc\Helpers\Collection;

/**
 * Builder class.
 */
class Builder {

	use Helper;

	/**
	 * Array value.
	 *
	 * @var array
	 */
	private $array = [];

	/**
	 * Constructor
	 *
	 * @param string $array The array name.
	 */
	public function __construct( $array ) {
		$this->array = $array;
		$this->reset();
	}

	public function is_multi() {
		return is_array($this->array[0]);
	}

	public function unique( $key = null ) {

		if(is_callable($key)){
			// todo: 
			return $this;
		}

		if($this->is_multi()){
		    $key_array = array();
			$temp_array = array();
		    foreach($this->array as $val) {
		        if (!in_array($val[$key], $key_array)) {
		            $key_array[] = $val[$key];
		            $temp_array[] = $val;
		        }
		    }
		    $this->array = $temp_array;
			return $this;
		}

		$this->array = array_unique ( $this->array );
		return $this;
	}

	public function where($key, $op_or_val, $key_value = null) {
		if(is_callable($key)){
			// todo: 
			return $this;
		}

		if(is_null($key_value)) {
			$key_value = $op_or_val; // default =
			$op_or_val = '==';
		}

		$temp_array = array();

		if(!$this->is_multi()){
			foreach($this->array as $k => $v) {
				if($k == $key && Helper::operatorCompare($v, $op_or_val, $key_value)) {
					$temp_array[] = $this->array[$k];
				}
			}
		} else {
			foreach($this->array as $m_array) {
				if(Helper::operatorCompare($m_array[$key], $op_or_val, $key_value)) {
					$temp_array[] = $m_array;
				}
			}
		}

		$this->array = $temp_array;
		return $this;
	}

	/* 
		==============================================
			Transformers
		==============================================
	*/	

	public function count( $key = null ) {
		if(is_callable($key)){
			// todo: 
			return $this;
		}

		if($this->is_multi()){
		    return count(array_column($this->array, $key));
		}

		return count ( $this->array );
	}

	public function sum( $key = null ) {
		if(is_callable($key)){
			// todo: 
			return $this;
		}

		if($this->is_multi()){
		    return array_sum(array_column($this->array, $key));
		}

		return array_sum ( $this->array );
	}

	public function all() {
		return $this->array;
	}

	public function first() {
		if(count($this->array) < 1) {
			return null;
		}

		return array_values($this->array)[0];
	}

	public function nth($position, $default = null) {
		if(count($this->array) < 1 || empty($this->array[$position])) {
			return $default;
		}

		return $this->array[$position];
	}

	public function last() {
		$cnt = count($this->array);

		if($cnt < 1) {
			return null;
		}

		return array_values($this->array)[$cnt - 1];
	}


	/**
	 * Reset all vaiables.
	 *
	 * @return self The current query builder.
	 */
	private function reset() {
		return $this;
	}
}
