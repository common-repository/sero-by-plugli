<?php

namespace Sero\Inc\Helpers\Console;

use Sero\Inc\Helpers\Config;
use Sero\Inc\Helpers\Database\Database;
use Sero\Inc\Helpers\Collection\Collection;

/**
 * ConsoleFilter class.
 */
class Console_Helper {

    private $client;

    private $type;
    private $limit;
    private $device;
    private $search_type;

    public function __construct($opts = []) {
        $this->init($opts);     
    }

    private function init ($opts = []) {
        $this->client = new Console();
        $opts = Collection::collect($opts);

        $this->type = $opts->nth('type', 'query');
        $this->limit = $opts->nth('limit', 25000);
        $this->device =$opts->nth('device', 'all');
        $this->search_type = $opts->nth('search_type', 'web');

        
        $this->client->dimension($this->type)
                        ->search_type($this->search_type)
                        ->limit($this->limit);
        
        if($this->device !== 'all'){
            $this->client->add_filter('device', strtoupper($this->device));
        }
    }

    public function is_comparable ($period) {
        // todo: make sure it it of type client
        return !is_null($period['from']);
    }

    public function set_client ($client) {
        // todo: make sure it it of type client
        $this->client = $client;
        return $this;
    }

    public function get_client () {
        return $this->client;
    }

    public function get_period ($from, $to = null) {
        $period_1 = explode(" ", $from);
        $period_2 = is_null($to)? false : explode(" ", $to);

        return [
            'from' => $period_2? $period_1 : null, // previous
            'to' => $period_2? $period_2 : $period_1, // current
        ];
    }

    public function get_data($from, $to = null, $client = null) {
        if(!is_null($client)) {
            $this->get_client($client);
        }
        
        $period = $this->get_period($from, $to);

        $period_1 = $period['from'];
        $period_2 = $period['to'];

        $rows_2 = (clone $this->client)->range($period_2[0], $period_2[1])->get();
        $rows_1 = is_null($period_1)? null : (clone $this->client)->range($period_1[0], $period_1[1])->get();

        return [$rows_2, $rows_1];
    }

    public function get_query_data($from, $to = null) {
        $data = $this->get_data($from, $to, 'query');
        return $data;
    }

    public function get_page_data($from, $to = null) {
        $data = $this->get_data($from, $to, 'page');
        return $data;
    }

    public function count_data($from, $to = null) {
        $data =  $this->get_data($from, $to);
        $interface = ['clicks' => 0, 'impressions' => 0, 'ctr' => 0, 'position' => 0, 'count' => 0];

        $sum_1 = $interface;
        $sum_2 = $interface;

        foreach ($data[0] as $item) {
            $sum_1['clicks'] += (int) $item['clicks'];
            $sum_1['impressions'] += (int) $item['impressions'];
            $sum_1['ctr'] += $item['ctr'];
            $sum_1['position'] += $item['position'];
            $sum_1['count'] += 1;
        }


        if(!is_null($data[1])) {
            foreach ($data[1] as $item) {
                $sum_2['clicks'] += (int) $item['clicks'];
                $sum_2['impressions'] += (int) $item['impressions'];
                $sum_2['ctr'] += $item['ctr'];
                $sum_2['position'] += $item['position'];
                $sum_2['count'] += 1;
            }
        }

        return [
            'current' => $sum_1,
            'previous' => $sum_2
        ];
    }

    public function to_string($data) {
        echo '<table class="table text-center">';
            echo '<thead>';
                echo '<tr>';
                    echo '<td>Property</td>';
                    echo '<td>Clicks</td>';
                    echo '<td>Impressions</td>';
                    echo '<td>CTR</td>';
                    echo '<td>Position</td>';
                    echo '<td>Missed Clicks</td>';
                echo '</tr>';
            echo '<tbody>';
                foreach ($data as $item) :
                    echo '<tr>';
                        echo '<td>';
                            echo $item['property'];
                        echo '</td>';
                        echo '<td>';
                            echo $item['clicks'];
                        echo '</td>';
                        echo '<td>';
                            echo $item['impressions'];
                        echo '</td>';
                        echo '<td>';
                            echo $item['ctr'];
                        echo '</td>';
                        echo '<td>';
                            echo $item['position'];
                        echo '</td>';
                        echo '<td>';
                            echo $item['missed_clicks'];
                        echo '</td>';
                    echo '</tr>';
                endforeach;
            echo '</tbody>';
        echo '<table>';
    }
}
