<?php

namespace Sero\Inc\Admin\Partials\Groups;

use \Exception;
use Sero\Inc\Helpers\Ajax;
use Sero\Inc\Helpers\Request;
use Sero\Inc\Helpers\Database\Database;
use Sero\Inc\Helpers\Collection\Collection;
use Sero\Inc\Helpers\Console\Console_Helper;

trait AjaxActions {
	private function get_client ($type) {
        $console = new Console_Helper([
            'limit' => -1,
            'type' => $type,
            'device' => Request::get('device', 'all'),
            'search_type' => Request::get('searchType', 'web')
        ]);

        return $console;
    }

    public function get_groups() {
        try { 
            $pages = [];
            $keywords = [];
            $results = Database::table("sero_groups")->where('is_active', 1)->get();

            foreach ($results as $group) {
                $postID = $group->post_id;
                $post = [
                    'id' => $group->id,
                    'type' => $group->type,
                    'date' => $group->date,
                    'past_size' => $group->past_size,
                    'present_size' => $group->present_size,
                    'is_active' => (int)$group->is_active,
                    'title' => $group->title,
                ];

                if($group->type == 1) {
                    $keywords[] = $post;
                } else {
                    $pages[] = $post;
                }
            }

            Ajax::success([
                'all' => $results,
                'pages' => $pages,
                'keywords' => $keywords,
                'timeline' => $this->get_sc_date()
            ]);
        } catch (Exception $e) {
            Ajax::error($e->getMessage());
        }   
    }

    public function get_group() {
        try { 
            $groupID = Request::get('group');
            
            if(!$groupID) {
                throw new Exception("Error Processing Request", 1);
            }

            $group_data = Database::table("sero_groups")->where('id', $groupID)->one();
            if(is_null($group_data)) {
                throw new Exception("Error Processing Request", 1);
            }

            $filters = [];
            $conditions =  Database::table("sero_groups_coditions")->where('group_id', $groupID)->get();
            $cnt = count($conditions);
            for ($i=0; $i < $cnt; $i++) { 
                $cond = $conditions[$i];

                $filters['type'][]     = $cond->type;
                $filters['value'][]    = $cond->value;
                $filters['csvalue'][] = $cond->csvalue;
                $filters['operator'][] = $cond->operator;
            }

            $rows = $this->get_sc_data($group_data->type, $filters);

            // get last group stats
            if($group_data->present_size != $rows['present_size']) {
                Database::table("sero_groups")->where('id', $groupID)->set('present_size', $rows['present_size'])->update();
                $group_data->present_size = $rows['present_size'];
            }

            if($group_data->past_size != $rows['past_size']) {
                Database::table("sero_groups")->where('id', $groupID)->set('past_size', $rows['past_size'])->update();
                $group_data->past_size = $rows['past_size'];
            }

            Ajax::success([
                'group' => $group_data, 
                'conditions' => $filters,
                'sc_data' => [$rows['present'], $rows['past']],
            ]);
        } catch (Exception $e) {
            Ajax::error($e->getMessage());
        }   
    }

    public function add_group() {
        try { 
            $title = Request::post('title', '');
            $type = Request::post('type', 0);
            $conditions = Request::request('conditions', []);

            $rows = $this->get_sc_data($type, $conditions);

            $groupID = Database::table("sero_groups")->insert([
                    'title' => $title, 
                    'type' => $type, 
                    'present_size' => $rows['present_size'], 
                    'past_size' => $rows['past_size']
                ], [ '%s', '%s', '%d', '%d']);


            $cnt = count($conditions['type']);
            for ($i=0; $i < $cnt; $i++) {
                Database::table("sero_groups_coditions")->insert([
                        'group_id' => $groupID, 
                        'type' => $conditions['type'][$i], 
                        'operator' => $conditions['operator'][$i], 
                        'value' => $conditions['value'][$i],
                        'csvalue' => $conditions['csvalue'][$i]
                    ], [ 
                        '%d',
                        '%s', 
                        '%s', 
                        '%s',
                        '%s'
                ]);
            }

            Ajax::success([
                'conditions' => $conditions,
                'group' => [
                    'id' => $groupID,
                    'title' => $title,
                    'type' => $type,
                    'is_active' => 1,
                    'date' => date(SERO_DATE_FORMAT),
                    'present_size' => $rows['present_size'], 
                    'past_size' => $rows['past_size']
                ],
                'sc_data' => [$rows['present'], $rows['past']], 
            ]);
        } catch (Exception $e) {
            Ajax::error($e->getMessage());
        }   
    }

    public function edit_group() {
        try { 
            $groupID = Request::get('group');

            if (is_null($groupID)) {
                throw new Exception("Error Processing Request", 1);
            }

            Ajax::success([
            ]);
        } catch (Exception $e) {
            Ajax::error($e->getMessage());
        }   
    }

    public function delete_group() {
        try { 
            $groupID = Request::get('group', null);

            if (is_null($groupID)) {
                throw new Exception("Error Processing Request", 1);
            }

            Database::table("sero_groups_coditions")->where('group_id', $groupID)->delete();
            Database::table("sero_groups")->where('id', $groupID)->delete();

            Ajax::success([
                'success' => true,
                'group' => $groupID,
            ]);
        } catch (Exception $e) {
            Ajax::error($e->getMessage());
        }   
    }

    private function get_sc_date () {
        $date_from = date('Y-m-d', strtotime('today -63 days')).' '.date('Y-m-d', strtotime('today -33 days'));
        $date_to = date('Y-m-d', strtotime('today -32 days')).' '.date('Y-m-d', strtotime('today -2 days'));

        return ['from' => $date_from, 'to' => $date_to];
    }

    private function get_sc_data ($type, $filters = []) {
        $period = $this->get_sc_date();

        $date_from = date('Y-m-01', strtotime('-1 month')).' '.date('Y-m-t', strtotime('-1 month'));
        $date_to = date('Y-m-01').' '.date('Y-m-t');

        $client = $this->get_client($type ==0? 'page' : 'query');
        $data =  $client->get_data($period['from'], $period['to']);

        $current = $data[0];
        $previous = $data[1];

        $rows_1 = [];
        $rows_2 = [];

        for ($i=0; $i < count($filters['type']); $i++) { 
            $property = ( $filters['type'][$i] == 'query' || $filters['type'][$i] == 'page_url' )? 'property' : $filters['type'][$i];
            
            foreach ($current as $item) {
                $f_item = $this->filter_sc_data($item, $property, $filters['operator'][$i], $filters['value'][$i], $filters['csvalue'][$i]);
                if(!is_null($f_item)) {
                    $rows_1[] = $f_item;
                }
            }

            foreach ($previous as $item) {
                $f_item = $this->filter_sc_data($item, $property, $filters['operator'][$i], $filters['value'][$i], $filters['csvalue'][$i]);
                if(!is_null($f_item)) {
                    // check if exists in current
                    $rows_2[] = $f_item;
                }
            }
        }

        return [
            'present_filtered' => $rows_1,
            'past_filtered' => $rows_2,
            'present_size' => count($rows_1),
            'past_size' => count($rows_2),
            'present' => $current,
            'past' => $previous
        ];
    }

    private function filter_sc_data($item, $property, $operator, $value, $cs_value = null) {
        if(!$item || !isset($item[$property])) return null;

        $rows = null;
        switch ($operator) {
            case 'equal':
                if($item[$property] == $value){
                    $rows = $item;
                }
                break;
            case 'not_equal':
                if($item[$property] != $value){
                    $rows = $item;
                }
                break;
            case 'less':
                if($item[$property] < $value){
                    $rows = $item;
                }
                break;
            case 'less_equal':
                if($item[$property] <= $value){
                    $rows = $item;
                }
                break;
            case 'greater':
                if($item[$property] > $value){
                    $rows = $item;
                }
                break;
            case 'greater_equal':
                if($item[$property] >= $value){
                    $rows = $item;
                }
                break;
            case 'between':
                if($item[$property] > $value && $item[$property] < $cs_value){
                    $rows = $item;
                }
                break;
            case 'not_between':
                if($item[$property] < $value && $item[$property] > $cs_value){
                    $rows = $item;
                }
                break;
        }

        return $rows;
    }
}