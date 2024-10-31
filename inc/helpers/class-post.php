<?php
namespace Sero\Inc\Helpers;

/**
 * Minimal Post Helper.
 *
 * @since      1.0.1
 * @package    Sero
 * @subpackage Sero\admin
 * @author     Gbenga Medunoye <ola_leykan@yahoo.com>
**/


use Sero\Inc\Helpers\Collection\Collection;

/**
 * Post class.
 */
class Post{
	public static function clean_url($property) {
    	$url_parts = parse_url($property);
        $cleaned = rtrim($url_parts['scheme'] . '://' . $url_parts['host'] . (isset($url_parts['path'])?$url_parts['path']:''), "/");
		return strtolower($cleaned);
    }

    public static function word_count($content) {
    	if($content == '')
    		return 0;
    	
	    $content = preg_replace('/(<\/[^>]+?>)(<[^>\/][^>]*?>)/', '$1 $2', $content);
	    $content = strip_tags(nl2br($content));

	    if (preg_match("/[\x{4e00}-\x{9fa5}]+/u", $content)) {
	        $content = preg_replace('/[\x80-\xff]{1,3}/', ' ', $content, -1, $n);
	        $n += str_word_count($content);

	        return $n;
	    } else {
	        return count(preg_split('/\s+/', $content));
	    }
	}

	public static function get_from_sc($merge_rows, $category = null, $type = null) {
        if(is_null($merge_rows)) {
            return null;
        }
        
        $ret_rows= []; 
        $handled_pages = [];

        foreach ($merge_rows as $page) {
            $prop = self::clean_url($page['property']);
            if(!array_key_exists($prop, $handled_pages)){
                $postID = url_to_postid($prop);
                if($postID !== 0) {
                    $post = get_post($postID);
                    $handled_pages[$prop]['post'] = [
                        'id' => $postID,
                        'post_title' => $post->post_title,
                        'post_type' => $post->post_type,
                        'post_content' => $post->post_content,
                        'date' => get_the_date(SERO_DATE_FORMAT, $postID),
                        'date_modified' => get_the_modified_date(SERO_DATE_FORMAT, $postID),
                        'word_count' => self::word_count($post->post_content),
                    ];
                }
            }

            $handled_pages[$prop]['sc_data'][] = $page;
        }

        foreach ($handled_pages as $k => $page) {
            $sc_data = Collection::collect($page['sc_data']);

            if(isset($page['post'])) {
                if(!is_null($category) && $category != -1) {
                    $categories = wp_get_post_categories($page['post']['id']);
                    if(!in_array($category, $categories))
                        continue;
                }

                if(!is_null($type) && $type != -1) {
                    if($page['post']['post_type'] != $type)
                        continue;
                }

                $ret_rows[] = [
                    'property' => $page['post']['post_title'],
                    'clicks' => $sc_data->sum('clicks'),
                    'ctr' => $sc_data->sum('ctr') / $sc_data->count('property') * 100,
                    'position' => $sc_data->sum('position') / $sc_data->count('property'),
                    'impressions' => $sc_data->sum('impressions'),
                    'missed_clicks' => $sc_data->sum('missed_clicks'),

                    'view_link' => $k,
                    'edit_link' => get_edit_post_link($page['post']['id'], null),

                    'id' => $page['post']['id'],
                    'post_type' => $page['post']['post_type'],
                    'date' => strtotime($page['post']['date']),
                    'permalink' => get_the_permalink($page['post']['id']),
                    'categories' => wp_get_post_categories($page['post']['id']),
                    'date_modified' => strtotime($page['post']['date_modified']),

                    'word_count' => self::word_count($page['post']['post_content']),
                    'images' => count( get_attached_media( 'image', $page['post']['id'] ) ),
                    'videos' => count( get_attached_media( 'video', $page['post']['id'] ) ) 
                    //substr_count($page['post']['post_content'], '<video'),
                ];
            }
        }

        $orphan_posts = get_posts([
            'numberposts' => -1,
            'exclude' => array_column($ret_rows, 'id')
        ]);

        foreach ($orphan_posts as $k => $post) {
            if(!is_null($category)) {
                $categories = wp_get_post_categories($post->ID);
                if(!in_array($category, $categories))
                    continue;
            }

            if(!is_null($type)) {
                if($post->post_type != $type)
                    continue;
            }

            $ret_rows[] = [
                'is_orphan' => 1,

                'property' => $post->post_title,
                'clicks' => 0,
                'ctr' => 0,
                'position' => 0,
                'impressions' => 0,
                'missed_clicks' => 0,

                'view_link' => get_post_permalink($post->ID),
                'edit_link' => get_edit_post_link($post->ID, null),

                'id' => $post->ID,
                'post_type' => $post->post_type,
                'date' => strtotime($post->post_date),
                'permalink' => get_the_permalink($post->ID),
                'categories' => wp_get_post_categories($post->ID),
                'date_modified' => strtotime(get_the_modified_date(SERO_DATE_FORMAT, $post->ID)),

                'word_count' => self::word_count($post->post_content),
                'images' => count( get_attached_media( 'image', $post->ID ) ),
                'videos' => count( get_attached_media( 'video', $post->ID ) ),
            ];
        }

        return $ret_rows;
    }
}
