<?php

namespace Sero\Inc\Admin\Partials\Queries;

use Exception;
use Sero\Inc\Helpers\Ajax;
use Sero\Inc\Helpers\Config;
use Sero\Inc\Helpers\Request;
use Sero\Inc\Helpers\Collection\Collection;
use Sero\Inc\Helpers\Console\Console_Helper;

trait AjaxActions {
	private function get_client ($type = 'query') {
        $console = new Console_Helper([
            'limit' => -1,
            'type' => $type,
            'device' => Request::get('device', 'all'),
            'search_type' => Request::get('searchType', 'web')
        ]);

        return $console;
	}

    public function sero_queries_get_query_count() {
        try {
            $date_from = Request::get('from');
            $date_to = Request::get('to', null);

            $client =  $this->get_client('date');
            $ret_rows = $client->count_data($date_from, $date_to);

            Ajax::success([
                'data' => $ret_rows,
            ]);
        } catch (Exception $e) {
            Ajax::error($e->getMessage());
        }   
    }

	public function sero_queries_get_query_data() {
		try {
            $date_from = Request::get('from');
            $date_to = Request::get('to', null);

            $client =  $this->get_client();
            $ret_rows = $client->get_query_data($date_from, $date_to);

            Ajax::success([
                'data' => $ret_rows,
            ]);
        } catch (Exception $e) {
            Ajax::error($e->getMessage());
        }   
	}

    public function sero_queries_get_query_summary() {
        try { 
            $query = Request::get('query');
            $date_from = Request::get('from');
            $date_to = Request::get('to', null);

            if(!$query)
                throw new Exception("Error Processing Request");

            $console = $this->get_client('page');
            $ret_rows = $console
                ->set_client($console->get_client()->add_filter('query', $query))
                    ->get_data($date_from, $date_to);

            Ajax::success([
                'data' => $ret_rows,
            ]);
        } catch (Exception $e) {
            Ajax::error($e->getMessage());
        }
    }
}
