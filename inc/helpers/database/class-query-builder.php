<?php
/**
 * The Query Builder.
 *
 * @since      1.0.0
 * @package    Sero
 * @subpackage Sero\Helpers\Database
 * @author     Sero <laxusgooee@gmail.com>
 */

namespace Sero\Inc\Helpers\Database;

/**
 * Query_Builder class.
 */
class Query_Builder {

	use Escape;
	use Select;
	use Where;
	use GroupBy;
	use OrderBy;
	use Translate;

	/**
	 * Table name.
	 *
	 * @var string
	 */
	public $table = '';

	/**
	 * Save last query.
	 *
	 * @var string
	 */
	public $last_query = '';

	/**
	 * Make a distinct selection
	 *
	 * @var bool
	 */
	protected $distinct = false;

	/**
	 * Make SQL_CALC_FOUND_ROWS in selection
	 *
	 * @var bool
	 */
	protected $found_rows = false;

	/**
	 * The query statements.
	 *
	 * @var array
	 */
	protected $statements = [];

	/**
	 * The query limit
	 *
	 * @var int
	 */
	protected $limit = null;

	/**
	 * Constructor
	 *
	 * @param string $table The table name.
	 */
	public function __construct( $table ) {
		$this->table = $table;
		$this->reset();
	}

	/**
	 * Translate the given query object and return the results
	 *
	 * @param string $output (Optional) Any of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K constants.
	 *
	 * @return mixed
	 */
	public function get( $output = \OBJECT ) {
		global $wpdb;

		$this->last_query = $this->translateSelect();
		$this->reset();

		return $wpdb->get_results( $this->last_query, $output ); // phpcs:ignore
	}

	/**
	 * Translate the given query object and return the results
	 *
	 * @param string $output (Optional) Any of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K constants.
	 *
	 * @return mixed
	 */
	public function one( $output = \OBJECT ) {
		global $wpdb;

		$this->limit( 1 );
		$this->last_query = $this->translateSelect();
		$this->reset();

		return $wpdb->get_row( $this->last_query, $output ); // phpcs:ignore
	}

	/**
	 * Translate the given query object and return one variable from the database
	 *
	 * @return mixed
	 */
	public function getVar() { // @codingStandardsIgnoreLine
		$row = $this->one( \ARRAY_A );

		return is_null( $row ) ? false : current( $row );
	}

	/**
	 * Insert a row into a table
	 *
	 * @codeCoverageIgnore
	 * @see wpdb::insert()
	 *
	 * @param array $data   Data to insert (in column => value pairs). Both $data columns and $data values should be "raw" (neither should be SQL escaped).
	 * @param array $format (Optional) An array of formats to be mapped to each of the value in $data.
	 *
	 * @return mixed
	 */
	public function insert( $data, $format = null ) {
		global $wpdb;

		$wpdb->insert( $this->table, $data, $format );

		return $wpdb->insert_id;
	}

	public function insertMultiple($columns, $data, $format = null) {
		global $wpdb;

		$rows = [];
		$formats = [];
		foreach ($data as $key => $value) {
			$row = "(";
			for ($i = 0; $i < count($columns); $i++) {
				// $val = $value[$columns[$i]];
				// if($format[$i] == '%s')
				// 	$val =  "'". urlencode($val) ."'";
				$val = isset($format[$i])? $format[$i] : '%s';
				$row .= $val.",";
				$formats[] =  $value[$columns[$i]];
			}
			$rows[] = rtrim($row, ","). ")";
		}

		$wpdb->query(
			$wpdb->prepare("INSERT INTO ". $this->table ." (". implode(",", $columns) .") VALUES ". implode(",", $rows) .";", $formats)
		);

		if($wpdb->last_error !== '') :
			$query = htmlspecialchars( $wpdb->last_query, ENT_QUOTES );
			print "<div id='error'>
			        <p class='wpdberror'>
			        	<strong>WordPress database error:</strong><br />
			        	<code>$query</code>
			        </p>
		        </div>";
			return false;
		endif;

		return true;
	}

	/**
	 * Update a row into a table
	 *
	 * @codeCoverageIgnore
	 *
	 * @return mixed
	 */
	public function update() {

		$query = $this->translateUpdate();
		$this->reset();

		return $this->query( $query );
	}

	/**
	 * Delete data from table
	 *
	 * @codeCoverageIgnore
	 *
	 * @return mixed
	 */
	public function delete() {

		$query = $this->translateDelete();
		$this->reset();

		return $this->query( $query );
	}

	/**
	 * Truncate table.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return mixed
	 */
	public function truncate() {
		return $this->query( "truncate table {$this->table};" );
	}

	/**
	 * Get found rows.
	 *
	 * @return int
	 */
	public function get_found_rows() {
		global $wpdb;

		return $wpdb->get_var( 'SELECT FOUND_ROWS();' );
	}

	/**
	 * Perform a MySQL database query, using current database connection.
	 *
	 * @codeCoverageIgnore
	 *
	 * @see wpdb::query
	 *
	 * @param string $query Database query.
	 *
	 * @return int|false Number of rows affected|selected or false on error.
	 */
	public function query( $query ) {
		global $wpdb;
		$this->last_query = $query;

		return $wpdb->query( $query ); // phpcs:ignore
	}

	/**
	 * Set the limit clause.
	 *
	 * @param int $limit  Limit size.
	 * @param int $offset Offeset.
	 *
	 * @return self The current query builder.
	 */
	public function limit( $limit, $offset = 0 ) {
		global $wpdb;
		$limit  = \absint( $limit );
		$offset = \absint( $offset );

		$this->limit = $wpdb->prepare( 'limit %d, %d', $offset, $limit );

		return $this;
	}

	/**
	 * Create an query limit based on a page and a page size
	 *
	 * @param int $page Page number.
	 * @param int $size Page size.
	 *
	 * @return self The current query builder.
	 */
	public function page( $page, $size = 25 ) {
		$size   = \absint( $size );
		$offset = $size * \absint( $page );

		$this->limit( $size, $offset );

		return $this;
	}

	/**
	 * Set values for insert/update
	 *
	 * @param string|array $name  Key of pair.
	 * @param string|array $value Value of pair.
	 *
	 * @return self The current query builder.
	 */
	public function set( $name, $value = null ) {
		if ( is_array( $name ) ) {
			$this->statements['values'] = $this->statements['values'] + $name;

			return $this;
		}

		$this->statements['values'][ $name ] = $value;

		return $this;
	}

	/**
	 * Reset all vaiables.
	 *
	 * @return self The current query builder.
	 */
	private function reset() {
		$this->distinct   = false;
		$this->found_rows = false;
		$this->limit      = null;
		$this->statements = [
			'select' => [],
			'wheres' => [],
			'orders' => [],
			'values' => [],
			'groups' => [],
			'having' => '',
		];

		return $this;
	}
}
