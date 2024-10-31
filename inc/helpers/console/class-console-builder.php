<?php

namespace Sero\Inc\Helpers\Console;


/**
 * SearchConsole class.
 */
trait Console_Builder {
    private function handle($start_date, $end_date) {
        
    }

    public function device($device) {
        $this->device = $device;
        return $this;
    }

    public function limit($limit) {
        $this->limit = $limit;
        return $this;
    }

    public function offset($offset) {
        $this->offset = $offset;
        return $this;
    }

    public function paginate($limit, $offset) {
        $this->limit = $limit;
        $this->offset = $offset;
        return $this;
    }

    public function dimension($dimension = [], $overwrite = false) {
        if(!is_array($dimension)){
            $dimension = explode(",", $dimension);
        }
        if($overwrite) {
            $this->dimensions = $dimension;
            return $this;
        }
        $this->dimensions = wp_parse_args( $dimension, $this->dimensions );
        return $this;
    }

    public function from($date) {
        $this->start_date = $date;
        return $this;
    }

    public function to($date) {
        $this->end_date = $date;
        return $this;
    }

    public function range($start_date, $end_date = false) {
        $this->start_date = $start_date;
        $this->end_date = !$end_date? date(SERO_DATE_FORMAT) : $end_date;
        return $this;
    }

    public function search_type($search_type) {
        $this->search_type = $search_type;
        return $this;
    }

    public function group_type($group_type) {
        $this->group_type = $group_type;
        return $this;
    }
    
    public function add_filter($dimension, $expression, $operator = "equals") {
        if(!isset($this->filters)){
            $this->filters = [];
        }
        $this->filters[] = [ 
            'operator' => $operator,
            'dimension' => $dimension, 
            'expression' => $expression
        ];
        return $this;
    }

    public function get() {
        $response = [];
        $limit = $this->limit;
        $offset = $this->offset;

        $rounds = 0;
        $batch_size = 25000;
        do{
            if($this->limit === -1) {
                $qsize = $batch_size; // infinite loop
            } else {
                $limit_left = $this->limit - $offset;
                $qsize = $limit_left < $batch_size? $limit_left : $batch_size;
            }

            $res = $this->get_query_data(
                $this->start_date, 
                $this->end_date,  
                $qsize,
                $offset,
                $this->dimensions, 
                $this->search_type,
                $this->filters
            );

            if(!isset( $res['rows'] ))
                break;

            // echo "<br /> *: ".$offset."- ".($offset + $qsize)." with ".count($res['rows'])."<br />";
            
            $response = array_merge($response, $res['rows']);
            $rounds++;

            if (count($res['rows']) < $batch_size)
                break;

            $limit =  ($this->limit === -1)? 1 : $limit - $batch_size;
            $offset =  $offset + $batch_size;
        } while ($limit > 0);

        // echo "<br />". $rounds.': '. count($response) ."<br />";

        // $start = microtime(TRUE);
        foreach ($response as &$row) {
            for ($i = 0; $i < count($this->dimensions); $i++) {
                $label = $i === 0? 'property' : $this->dimensions[$i];
                $row[$label] = $row['keys'][$i];
            }

            $row['missed_clicks'] = $row['impressions'] - $row['clicks'];
            unset($row['keys']);
        }
        // echo "The code took " . (microtime(TRUE) - $start) . " seconds to complete.<br />";
        $this->reset();
        return $response;
    }

    public function get_raw() {
        
    }

    private function reset() {
        $this->limit        = 5;
        $this->offset       = 0;
        $this->filters      = [];
        $this->end_date     = null;
        $this->start_date   = null;
        $this->dimensions   = [];

        return $this;
    }
}
