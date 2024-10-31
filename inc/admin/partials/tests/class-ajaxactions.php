<?php

namespace Sero\Inc\Admin\Partials\Tests;

use Exception;
use Sero\Inc\Helpers\Ajax;
use Sero\Inc\Helpers\Post;
use Sero\Inc\Helpers\Request;
use Sero\Inc\Helpers\Database\Database;
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

    private function get_post_data($post, $page = null) {
        $postID = $post->ID;
        if(is_null($page)) {
            $page = get_the_permalink($postID);
        }
        $rev_data = wp_get_post_revisions($postID, array(
            // 'date_query' => array(
            //     array(
            //         'after'     => 'January 1st, 2015',
            //         'before'    => 'December 31st, 2015',
            //         'inclusive' => true,
            //     ),
            // ),
        ));

        return [
            'id' => $postID,
            'permalink' => $page,
            'title' => $post->post_title,
            'type' => $post->post_type,
            'content' => $post->post_content,
            'editlink' => get_edit_post_link($post->ID, null),
            'date' => get_the_date(SERO_DATE_FORMAT, $postID),
            'date_modified' => get_the_modified_date(SERO_DATE_FORMAT, $postID),


            'revisions' => $rev_data,
            'word_count' => Post::word_count($post->post_content),
            'images' => count( get_attached_media( 'image', $postID ) ),
            'videos' => count( get_attached_media( 'video', $postID ) ) 
        ];
    }

    private function get_sc_data ($type = 'query', $from_date = null, $to_date = null, $page = null) {
        $to = is_null($to_date)? date(SERO_DATE_FORMAT, strtotime('-2 days')) : $to_date;
        $from = is_null($from_date)? date(SERO_DATE_FORMAT, strtotime('-32 days')) : $from_date;

        $console = $this->get_client($type);
        if(!is_null($page)) {
            $console->get_client()->add_filter('page', $page);
        }

        return $console->get_data($from.' '.$to);
    }

    public function get_tests() {
        try { 
            $ret_rows = [];
            $today = new \DateTime;
            $results = Database::table("sero_tests")->where('is_cancelled', 0)->get();

            foreach ($results as $test) {
                $postID = $test->post_id;
                $a = new \DateTime($test->date);
                $is_active = $test->is_active;

                if($is_active == 1 && $test->duration <= $a->diff($today)->days) {
                    $is_active = 0;
                }

                $ret_rows[] = [
                    'id' => $test->id,
                    'post_id' => $postID,
                    'date' => $test->date,
                    'duration' => $test->duration,
                    'is_active' => $is_active,
                    'property' => html_entity_decode(get_the_title($postID), ENT_QUOTES),
                    'permalink' => get_the_permalink($postID),
                ];
            }

            Ajax::success([
                'data' => $ret_rows,
            ]);
        } catch (Exception $e) {
            Ajax::error($e->getMessage());
        }   
    }

    public function add_test() {
        try { 

            $postID = Request::post('post', false);
            $note = Request::post('note', '');
            $duration = Request::post('duration', 15);

            if(!$postID || !is_numeric($postID) || $duration < 1) {
                throw new Exception("Error Processing post: {$postID}, with duration: {$duration}", 1);
            }

            $post = get_post($postID);
            $page = rtrim (get_the_permalink($postID), '/');

            // check if test already exist
            $test_data = Database::table("sero_tests")->where(['post_id' => $postID, 'is_active' => 1])->one();

            if(!is_null($test_data)) {
                throw new Exception("Test already exists", 1);
            }


            $testID = $test_data = Database::table("sero_tests")->insert([
                'post_id' => $postID, 
                'note' => $note, 
                'duration' => $duration, 
                'date' => date(SERO_DATE_FORMAT)
            ]);

            Ajax::success([
                'test_data' => [
                    'id' => $testID,
                    'post_id' => $postID, 
                    'note' => $note, 
                    'duration' => $duration, 
                    'date' => date(SERO_DATE_FORMAT),
                    'is_active' => 1,
                    'property' => get_the_title($postID),
                    'permalink' => get_the_permalink($postID),
                ],
                'post' => $this->get_post_data($post, $page),
                'page' => $page,
                'sc_data_date' => [],
                'sc_data' => [],
                'sc_data_date_previous' => [],
            ]);
        } catch (Exception $e) {
            Ajax::error($e->getMessage());
        } 
    }

    public function get_test() {
        try {

            $testID = Request::get('test');
            
            if(!$testID) {
                throw new Exception("Error Processing Request", 1);
            }

            $test_data = Database::table("sero_tests")->where('id', $testID)->one();
            if(is_null($test_data)) {
                throw new Exception("Error Processing Request", 1);
            }

            $post = get_post($test_data->post_id);
            $page = rtrim (get_the_permalink($test_data->post_id), '');

            $sc_date = [];
            $a = new \DateTime($test_data->date);
            $b = new \DateTime;

            if($test_data->is_active == 1 && $test_data->duration <= $a->diff($b)->days) {
                Database::table("sero_tests")->where('id', $test_data->id)->set('is_active', '0')->update();
                $test_data->is_active = 0;
            }

            if($a->diff($b)->days > 2) {
                $from = date(SERO_DATE_FORMAT, strtotime($test_data->date));
                $to = date(SERO_DATE_FORMAT, strtotime($test_data->date. ' + '. $test_data->duration. ' days'));

                $sc_date = $this->get_sc_data('date', $from, $to, $page);
            }

            $sc_data = $this->get_sc_data('query', null, null, $page);
            $sc_date_previous = $this->get_sc_data('date', null, null, $page);

        
            Ajax::success([
                'page' => $page,
                'sc_data' => $sc_data,
                'test_data' => $test_data,
                'sc_data_date' => $sc_date,
                'sc_data_date_previous' => $sc_date_previous,
                'post_data' => $this->get_post_data($post, $page),
            ]);
        } catch (Exception $e) {
            Ajax::error($e->getMessage());
        }   
    }

    public function update_test() {
        try { 
            $testID = Request::post('id');

            if(!$testID) {
                throw new Exception("Error Processing Request", 1);
            }

            $test_data = $test_data = Database::table("sero_tests")->where('id', $testID)->one();

            if(!is_null($test_data) && $test_data->is_active == 0) {
                throw new Exception("Can't perform this transaction", 1);
            }
            
            $note = Request::post('note', $test_data->note);
            $duration = Request::post('duration', $test_data->duration);

            $data = ['note' => $note, 'duration' => $duration];
            Database::table("sero_tests")->where('id', $testID)->set($data)->update();

            $test_data->note = $note;
            $test_data->duration = $duration;
            
            Ajax::success([
                'data' => $test_data,
            ]);
        } catch (Exception $e) {
            Ajax::error($e->getMessage());
        } 
    }

    public function cancel_test() {
    	try { 
	    	$testID = Request::post('id');

	    	if(!$testID) {
	    		throw new Exception("Error Processing Request", 1);
	    	}

	    	$test_data = $test_data = Database::table("sero_tests")->where('id', $testID)->one();

			if(!is_null($test_data) && $test_data->is_active == 0) {
	    		throw new Exception("Can't perform this transaction", 1);
	    	}

	    	$data = ['is_active' => '0', 'is_cancelled' => '1'];
	    	Database::table("sero_tests")->where('id', $testID)->set($data)->update();

            Ajax::success([]);
        } catch (Exception $e) {
            Ajax::error($e->getMessage());
        } 
    }

    public function get_posts() {
        global $wpdb;
        try { 
            $ret_rows = [];
            $ret_rows = get_posts(['numberposts' => -1]);
            
            Ajax::success([
                'data' => $ret_rows,
                'sc_data' => [],
            ]);
        } catch (Exception $e) {
            Ajax::error($e->getMessage());
        }   
    }

    public function get_post() {
        try { 
            $postID = Request::get('post');
            
            if(!$postID) {
                throw new Exception("Error Processing Request", 1);
            }

            $post = get_post($postID);
            $page = rtrim (get_the_permalink($postID), '');
            $test_data = Database::table("sero_tests")->where(['post_id' => $postID, 'is_cancelled' => 0])->orderBy('id', 'desc')->one();
            
            $sc_date = [];
            if(!is_null($test_data)) {
                $a = new \DateTime($test_data->date);
                $b = new \DateTime;

                if($test_data->is_active == 1 && $test_data->duration <= $a->diff($b)->days) {
                    Database::table("sero_tests")->where('post_id', $postID)->set('is_active', '0')->update();
                    $test_data->is_active = 0;
                }

                if($a->diff($b)->days > 2) {
                    $from = date(SERO_DATE_FORMAT, strtotime($test_data->date));
                    $to = date(SERO_DATE_FORMAT, strtotime($test_data->date. ' + '. $test_data->duration. ' days'));

                    $sc_date = $this->get_sc_data('date', $from, $to, $page);
                }
            }

            $sc_data = $this->get_sc_data('query', null, null, $page);
            $sc_date_previous = $this->get_sc_data('date', null, null, $page);
        
            Ajax::success([
                'sc_data' => $sc_data,
                'test_data' => $test_data,
                'sc_data_date' => $sc_date,
                'sc_data_date_previous' => $sc_date_previous,
                'post' => $this->get_post_data($post, $page),
                'page' => $page
            ]);
        } catch (Exception $e) {
            Ajax::error($e->getMessage());
        }   
    }
}