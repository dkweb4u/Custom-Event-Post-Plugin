<?php

/*
Plugin Name: My Events
Description: Event Post Type
Version: 1.0
Author: Dinesh
Author Uri: //ddk.netlify.app
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class myCustomPostTypeOfEvents{

    public function __construct(){

        add_action('admin_init', array($this,'em_check_required_plugins'));

        add_action('init', array($this,"eventpost"));
        
        add_action('admin_menu', array($this, 'settings_menu'));

        add_shortcode('dk_custom_events',array($this,'shortCodeOfEvents'));

    }

    function em_check_required_plugins() {
        if (!is_plugin_active('advanced-custom-fields/acf.php')) {
           
            deactivate_plugins(plugin_basename(__FILE__));
            
         
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error"><p>';
                esc_html_e('The "Events Manager" plugin requires the Advanced Custom Fields (ACF) plugin to be installed and active. Please install and activate Advanced Custom Fields (ACF) before activating this plugin.', 'text_domain');
                echo '</p></div>';
            });
    
           
            if (isset($_GET['activate'])) {
                unset($_GET['activate']);
            }
        }
    }

    function eventpost(){
        register_post_type('event',array(
            'public'=> true,
            'labels' => array(
                'name' => 'Events',
                'add_new_item' => 'Add New Event',
                'edit_item' => 'Edit Event',
                'all_items' => 'All Events',
                'singular_name' => 'Event'
            ),
            'rewrite' => ['slug' => 'events'],
            'menu_icon' => 'dashicons-calendar',
            'supports' => ['title', 'editor']
        ));
    }

    function settings_menu() {
        add_submenu_page(
            'edit.php?post_type=event', 
            'Event Settings',
            'Settings',
            'manage_options', 
            'event-setting', 
            array($this,'settingPage') 
        );
    }


    function settingPage(){
        ?>
        <style>
            .dk-event-custom-post input,
            .dk-event-custom-post select{
                width: 100px;
            }
            .dk-event-custom-post table{
               border-spacing: 10px;
            }
        </style>
       <div class="wrap">
        <h1>Event Shortcode Settings</h1>
        <p>The Shortcode is : <b class="eventshortcode">[dk_custom_events]</b></p>

        <div class="dk-event-custom-post">
        <table>
          <tr>
        <td>Posts Per page</td>
        <td>:</td>
        <td><input type="number" class="postperpage" value="5" min="1" max="10"></td>
        </tr>
        <tr>
            <td>
            Order By
            </td>
            <td>:</td>
            <td>
                <select name="orderby" class="orderby"id="">
                    <option value="asc">ASC</option>
                    <option value="desc">DESC</option>
                </select>

            </td>
        </tr>
        <tr>
            <td>
           Current Event 
            </td>
            <td>:</td>
            <td>
                <select name="currentevents" class="currentevents" id="">
                    <option value="">Upcoming</option>
                    <option value="old">Old</option>
                    <option value="all">All</option>
                </select>

            </td>
        </tr>
         </table>
        <button class="button button-primary">Copy</button>
    </div>

    
<script>
        
       
        let formdiv = document.querySelector('.dk-event-custom-post');
        let eventshortcode = document.querySelector('.eventshortcode');
        let eventdivCopybtn = document.querySelector('.dk-event-custom-post button.button-primary');
  



       

        function dksettingsChange(){
        let postperpage = document.querySelector('.dk-event-custom-post .postperpage');
        let orderby = document.querySelector('.dk-event-custom-post .orderby');
        let currentevents = document.querySelector('.dk-event-custom-post .currentevents');

         eventshortcode.innerText = `[dk_custom_events `;

         if(postperpage.value != 5){
            eventshortcode.innerText += ` posts_per_page="${postperpage.value}" `;
         }

         if(orderby.value != 'asc'){
            eventshortcode.innerText += ` order="${orderby.value}" `;
         }

         if(currentevents.value !=''){

        eventshortcode.innerText += ` current_events="${currentevents.value}" `;
            
        }
         
        eventshortcode.innerText += "]";


    }
       
        dksettingsChange();

        formdiv.addEventListener('click',()=>{
            dksettingsChange();
        });

        eventdivCopybtn.addEventListener('click',()=>{
            
            navigator.clipboard.writeText(eventshortcode.innerText);

            eventdivCopybtn.innerText = "Copied!";

            setTimeout(() => {
                eventdivCopybtn.innerText = "Copy";
            }, 2000);

        });


    </script>

       </div>
        <?php
     }

     function shortCodeOfEvents($atts){
        

        $atts= shortcode_atts(
            array(
            "posts_per_page" => 5,
            "order" => "asc",
            "current_events" => "" 
            ),
            $atts,
            ''
        );

        switch (strtolower($atts['current_events'])) {
            case 'all':
                $fillterEvent = [];          
                break;
                case 'old':
                    $fillterEvent =  array('key' => 'event_date',
                        'compare' => '<=',
                        'value' =>  date('Ymd'),
                        'type'=> 'numeric'
                       );
              
                    break;            
            
            default:
            $fillterEvent =  array('key' => 'event_date',
            'compare' => '>=',
            'value' =>  date('Ymd'),
            'type'=> 'numeric'
           );
                break;
        }

        $myEvents = new WP_Query(



            array(
                'post_type' => 'event',
                'posts_per_page' => $atts['posts_per_page'],
                'orderby' => 'meta_value_num',
                'order' => $atts['order'],
                'meta_key' => 'event_date',
                'meta_query'=> array( $fillterEvent       
                )
            )
        );
    
        $output = "<ul>";
        while($myEvents->have_posts()){
            $myEvents->the_post(); 

            $output .= "<li><a href='".get_the_permalink()."'>".get_the_title()."</a> - ".esc_html(get_field('event_date'))."</li>";
        }
        $output .= "</ul>";
        wp_reset_postdata();
        return $output;
    }
     

}

$myCustomEventPost = new myCustomPostTypeOfEvents();





