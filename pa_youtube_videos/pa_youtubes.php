<?php
/*  
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
Plugin Name: Youtube Video Thumbnails
Plugin URI: http://www.wordpress.org/
Description: Youtube video thumbnails from RSS feed. The actual video will be loaded inside jquery lightbox(if installed), or you can install one like this great plugin <a href="http://www.23systems.net/plugins/lightbox-plus/" target="_blank"><b>Lightbox Plus</b></a>
Author: Puaka Astro
Version: 1.0.0
Author URI: http://www.wordpress.org/
*/

define('PA_PLUGIN_PATH', WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)));

class PA_Youtube_Thumbnails extends WP_Widget {
    private $videos = array();
    private $youtube_user;
    private $total_items_to_display = 0;
    
    function __construct()
    {
        //WP_Widget($id_base = false, $name, $widget_options = array(), $control_options = array() )
        $widget_options     =   array(
            'classname'     =>  __('pa-youtube-thumbnails', 'payoutubethumb'),
            'description'   =>  __('Display youtube video list as thumbnail and load video in jquery lightbox(if installed, else will open in a new window/tab)', 'payoutubethumb')
        );
        $control_options    =   array();
        
        parent::__construct('pa_youtube_thumbnails', __('Youtube Video Thumbnails', 'payoutubethumb'), $widget_options, $control_options);
        
        $this->load_scripts();
    }

    function form($instance)
    {
        // outputs the options form on admin
        $instance = wp_parse_args((array)$instance, array(
            //'lightbox_style'    =>  'dark',
            'title'             =>  '',
            'total_thumbnails'  =>  4,
            'thumbnail_width'   =>  50, 
            'thumbnail_height'  =>  50,
            'youtube_user'      =>  ''
        ));
        
        $instance['title']              =   strip_tags($instance['title']);
        //$instance['lightbox_style']     =   strip_tags($instance['lightbox_style']);
        $instance['popup_width']        =   (is_numeric(strip_tags($instance['popup_width']))) ? intval(strip_tags($instance['popup_width']), 10) : 425;
        $instance['popup_height']       =   (is_numeric(strip_tags($instance['popup_height']))) ? intval(strip_tags($instance['popup_height']), 10) : 344;
        $instance['total_thumbnails']   =   (is_numeric(strip_tags($instance['total_thumbnails']))) ? intval(strip_tags($instance['total_thumbnails']), 10) : 0;
        $instance['thumbnail_width']    =   (is_numeric(strip_tags($instance['thumbnail_width'])))  ? intval(strip_tags($instance['thumbnail_width']), 10)  : 50;
        $instance['thumbnail_height']   =   (is_numeric(strip_tags($instance['thumbnail_height']))) ? intval(strip_tags($instance['thumbnail_height']), 10) : 0;
        $instance['youtube_user']       =   strip_tags($instance['youtube_user']);
        
        //@TODO different styling for each widget
        //Read style directory
        /*
        $style_path =   dirname(__FILE__).'/css/lightbox';
        $styles     =   array();
        
        if(!class_exists('DirectoryIterator'))
        {
            if ($handle =   opendir($style_path))
            {
                
                while (false !== ($file = readdir($handle)))
                {
                    echo (is_dir($style_path."/$file")) ? "$file Is Dir" : "$file is not dir", '<br>'; 
                    if ($file != "." && $file != ".." && $file != ".DS_Store" && $file != ".svn" && $file != "index.html")
                    {
                        $styles[$file] = $style_path."/".$file."/";
                    }
                }
                closedir( $handle );
            }
        }
        else
        {
            foreach (new DirectoryIterator($style_path) as $file_info)
            {
                if($file_info->isDot())
                {
                    continue;
                }
                
                if($file_info->isDir())
                {
                    $styles[$file_info->getFilename()] = realpath($file_info->getPathname()).DIRECTORY_SEPARATOR;
                }
            }
        }
        */
        ?>
        
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php echo _e('Widget Title :', 'payoutubethumb');?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('youtube_user'); ?>"><?php echo _e('Youtube User ID :', 'payoutubethumb');?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('youtube_user'); ?>" name="<?php echo $this->get_field_name('youtube_user'); ?>" type="text" value="<?php echo esc_attr($instance['youtube_user']); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('popup_width'); ?>"><?php echo _e('Popup Width :', 'payoutubethumb');?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('popup_width'); ?>" name="<?php echo $this->get_field_name('popup_width'); ?>" type="text" value="<?php echo esc_attr($instance['popup_width']); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('popup_height'); ?>"><?php echo _e('Popup Height :', 'payoutubethumb');?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('popup_height'); ?>" name="<?php echo $this->get_field_name('popup_height'); ?>" type="text" value="<?php echo esc_attr($instance['popup_height']); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('total_thumbnails'); ?>"><?php echo _e('Total Thumbnails :', 'payoutubethumb');?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('total_thumbnails'); ?>" name="<?php echo $this->get_field_name('total_thumbnails'); ?>" type="text" value="<?php echo esc_attr($instance['total_thumbnails']); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('thumbnail_width'); ?>"><?php echo _e('Thumbnail Width :', 'payoutubethumb');?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('thumbnail_width'); ?>" name="<?php echo $this->get_field_name('thumbnail_width'); ?>" type="text" value="<?php echo esc_attr($instance['thumbnail_width']); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('thumbnail_height'); ?>"><?php echo _e('Thumbnail Height :', 'payoutubethumb');?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('thumbnail_height'); ?>" name="<?php echo $this->get_field_name('thumbnail_height'); ?>" type="text" value="<?php echo esc_attr($instance['thumbnail_height']); ?>" />
            <br /><small><?php echo _e('Leave empty for width based thumbnail ratio.', 'payoutubethumb');?></small>
        </p>
        <?php 
        /* css cannot be multiple @TODO 
        <p>
            <?php echo _e('Choose style for lightbox. Default is <b>Dark</b>', 'payoutubethumb');?>
        </p>
        <p>
            <label><?php echo _e('Lightbox Style : ', 'payoutubethumb');?></label>
            <select name="<?php echo $this->get_field_name('lightbox_style'); ?>" id="<?php echo $this->get_field_id('lightbox_style'); ?>">
                <?php
                    $cur_style  =   $instance['lightbox_style'];
                    
                    foreach ($styles as $key => $value)
                    {
                        $style_name =   ucfirst(str_replace('.css', '', $key));
                        $key        =   urlencode($key);
                        echo '<option '.selected($key , $instance['lightbox_style']).' value="'.$key.'">'. __($style_name, 'payoutubethumb').'</option>';
                    }
                ?>
            </select>
        </p>
        */
        ?>
    <?php    
    }

    function update($new_instance, $old_instance) {
        // processes widget options to be saved
        $instance                       =   $old_instance;
        //$instance['lightbox_style']     =   strip_tags($new_instance['lightbox_style']);
        $instance['title']              =   strip_tags($new_instance['title']);
        $instance['popup_width']        =   (is_numeric(strip_tags($new_instance['popup_width']))) ? intval(strip_tags($new_instance['popup_width']), 10) : 425;
        $instance['popup_height']       =   (is_numeric(strip_tags($new_instance['popup_height']))) ? intval(strip_tags($new_instance['popup_height']), 10) : 344;
        $instance['total_thumbnails']   =   (is_numeric(strip_tags($new_instance['total_thumbnails']))) ? intval(strip_tags($new_instance['total_thumbnails']), 10) : 0;
        $instance['thumbnail_width']    =   (is_numeric(strip_tags($new_instance['thumbnail_width'])))  ? intval(strip_tags($new_instance['thumbnail_width']), 10)  : 50;
        $instance['thumbnail_height']   =   (is_numeric(strip_tags($new_instance['thumbnail_height']))) ? intval(strip_tags($new_instance['thumbnail_height']), 10) : 0;
        $instance['youtube_user']       =   strip_tags($new_instance['youtube_user']);
        return $instance;
    }

    function widget($args, $instance) {
        $plugin_path    =   dirname(__FILE__);
        // outputs the content of the widget
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        
        $loaded_styles = wp_print_styles();

        if(false === array_search('lightboxStyle', $loaded_styles))
        {
            $style_path     =   dirname(__FILE__).'/css/lightbox';
            $default_style  =   'elegant'; //@TODO multiple styling
            
            wp_register_style('lightbox_style', $style_path.'/'.$default_style.'/colorbox.css','', false,'screen');
            wp_enqueue_style('lightbox_style');
        }
        
        echo $before_widget;
        
        if ($title)
        {
            echo $before_title . $title . $after_title;
        }
        
        //get the videos
        $items  =   $this->get_videos($instance);
        
        if($items)
        {
            $thumb_width    =   (is_numeric($instance['thumbnail_width']) && $instance['thumbnail_width']) ? $instance['thumbnail_width'] : 80; //default to 80
            $width          =   "width=\"$thumb_width\"";
            $height         =   (is_numeric($instance['thumbnail_height']) && $instance['thumbnail_height']) ? "height=\"$instance[thumbnail_height]\"" : '';
            $thumbnails     =   '<ul class="pa-youtubethumb-list">';
            $popup_height   =   (isset($instance['popup_height']) && $instance['popup_height']) ? $instance['popup_height'] : 344;
            $popup_width    =   (isset($instance['popup_width']) && $instance['popup_width']) ? $instance['popup_width'] : 425;
            $total_thumbs   =   intval($instance['total_thumbnails'],10);
            $count          =   1;
            
            foreach($items as $video)
            {
                $thumbnails .=  "<li $class>".
                                    '<a href="'.$video['video_url'].'" target="_new" onclick="return PAYoutubeThumb.showVideo(this,'.$popup_width.','.$popup_height.');" title="'.__($video['title'], 'payoutubethumb').'">'.
                                        '<img src="'.$video['thumbnail'].'" '.$width.' '.$height.' border="0" alt="'.__($video['title'],'payoutubethumb').'" />'.
                                    '</a>'.
                                    '<span class="pa-video-play">'.$video['play_time'].'</span>'.
                                '</li>';
            }
            echo $thumbnails.'</ul>';
        }
        
        echo $after_widget;
    }
    
    private function get_videos($instance)
    {
        // Get RSS Feed(s)
        include_once(ABSPATH . WPINC . '/feed.php');

        $rss    = fetch_feed('http://gdata.youtube.com/feeds/base/users/'.$instance['youtube_user'].'/uploads?alt=rss&v=2&orderby=published&client=ytapi-youtube-profile');

        // Figure out how many total items there are, but limit it to widget setting. 
        try
        {
            $maxitems = $rss->get_item_quantity($instance['total_thumbnails']); 
        }
        catch(Exception $e)
        {
            return false;
        }
        // Build an array of all the items, starting with element 0 (first element).
        try
        {
            $rss_items  =   $rss->get_items(0, $maxitems); 
        }
        catch(Exception $e)
        {
            return false;
        }
        $data   =   array();
        
        if($maxitems)
        {
            foreach($rss_items as $item)
            {
                $items  =   array();
                
                $youtubeid = strchr($item->get_link(),'='); //split off the final bit of the URL beginning with '='
                $youtubeid = substr($youtubeid,1); //remove that equals sign to get the video ID
                
                $items['video_id']  =   $youtubeid;
                $items['title']     =   $item->get_title();
                
                #The URL
                $items['video_url']   =   "http://www.youtube.com/v/$youtubeid&hl=en&fs=1";
                
                //get thumbnail
                $thumb  =   preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $item->get_description(), $matches);
                //add it to array
                $items['thumbnail']   =   $matches[1][0];
                
                $temp   =   strip_tags($item->get_description(), '<span>');
                $temp   =   preg_match('/<span>Time.*\s.*/i', $temp, $matches);
                $temp   =   strip_tags($matches[0]);
                $temp   =   explode("\n", $temp);
                
                $items['play_time'] = (isset($temp[1])) ? trim($temp[1]) : '';
                $data[] = $items;
            }
            return $data;
        }
    }
    
    private function load_scripts()
    {
        if (!is_admin())
        {
            if(!wp_script_is('jquery'))
            {
                wp_register_script('jquery', ("http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"), false, '1.4.4');
                wp_enqueue_script('jquery');
            }
            
            if(!wp_script_is('lightbox', 'registered'))
            {
                wp_register_script('lightbox', PA_PLUGIN_PATH.'/js/jquery.colorbox-min.js', array('jquery'), '1.3.15', true);
                wp_enqueue_script('lightbox');
            }
            
            wp_register_script('payoutubescript', PA_PLUGIN_PATH.'/js/pa_youtube-min.js', array('jquery'), false, true);
            if(!wp_script_is('payoutubescript'))
            {
                wp_enqueue_script('payoutubescript');
            }
            
            wp_register_style('pa_youtubethumb_style', PA_PLUGIN_PATH.'/css/pa_youtubethumb.css','', false,'screen');
            wp_enqueue_style('pa_youtubethumb_style');
        }
    }
    
} // class PA_Youtube_Thumbnails

function pa_load_widgets() {
    register_widget('PA_Youtube_Thumbnails');
}
add_action('widgets_init', 'pa_load_widgets');
    
    
?>