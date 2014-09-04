<?php
/**
 * Plugin Name: Multi-Category Selection Widget RSS Feed
 * Plugin URI: http://github.com/saselberg/MultiCategorySelectionWidgetRSSFeed
 * Description: Adds a widget and some extra functionality to create rss feeds from the multi-category selection
 * Version: 1.0
 * Author: Scott Selberg
 * Author URI: http://github.com/saselberg
 * License: GPL2
 */

// This function looks for a cgi parameter and turns the default multi-category
// search filter logic from 'or' to 'and'
function mcswrf_category_filter( $query ) {
   if( isset( $_REQUEST['wpmm_operator'] ) && strtolower($_REQUEST['wpmm_operator']) == 'and' ){
      

      // the code below was leveraged from wp_includes/query.php
      $cat_and = array();
      $cat_array = preg_split( '/[,\s]+/', urldecode( $query->get('cat') ) );
      $cat_array = array_map( 'intval', $cat_array );
      foreach ( $cat_array as $cat ) {
         if ( $cat > 0 ) {
            $cat_and[] = $cat;
         }
      }
      
      //error_log( "text" . var_dump( $cat_and ) );
      $query->set( 'category__and', $cat_and );
      unset( $cat_array, $cat_and );
   }
}

// Create a widget to generate the Multi-Category RSS Feed
// leverage code from http://codex.wordpress.org/Widgets_API
class mcswrf_widget extends WP_Widget {

   function __construct() {
      parent::__construct( 'mcswrf_widget', // Base ID
                           __('Multi-Category Selection Widget RSS Feed', 'text_domain'), // Name
			   array( 'description' => __( 'Adds an rss feed for the current Multi-Category Selection', 'text_domain' ), ) // Args
      );
   }
	
   public function widget( $args, $instance ) {
      $title = apply_filters( 'widget_title', $instance['title'] );

      $rssImage = get_option('siteurl')."/wp-content/plugins/".dirname(plugin_basename(__FILE__))."/rss_small_icon.png";

      echo $args['before_widget'];
      if ( ! empty( $title ) ) {
         echo $args['before_title'] . $title . $args['after_title'];
      }

      $rssLink = get_bloginfo('url') . "?feed=rss2";
      if( isset( $_SESSION[ 'wpmm_cats' ] ) ){
         $rssLink .= "&cate".$_SESSION['wpmm_cats'];
      }
      
      if( isset( $_SESSION[ 'wpmm_search_vars' ] ) && strtolower($_SESSION[ 'wpmm_search_vars' ]) == 'and' ){
         $rssLink .= "&wpmm_operator=and";
      }
      
      echo "<a href=\"".$rssLink."\"><img alt=\"feed\" src=\"".$rssImage."\">&nbsp;".$instance['link']."</a>";
      echo $args['after_widget'];
   }

   public function form( $instance ) {
      if ( isset( $instance[ 'title' ] ) ) {
         $title = $instance[ 'title' ];
      } else {
         $title = __( 'Multi-Category Selection RSS Feed', 'text_domain' );
      }
      if ( isset( $instance[ 'link' ] ) ) {
         $link = $instance[ 'link' ];
      } else {
         $link = __( 'RSS Feed', 'text_domain' );
      }
      ?>
      <p>
      <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
      <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
      <br/>
      <label for="<?php echo $this->get_field_id( 'link' ); ?>"><?php _e( 'Link Text:' ); ?></label> 
      <input class="widefat" id="<?php echo $this->get_field_id( 'link' ); ?>" name="<?php echo $this->get_field_name( 'link' ); ?>" type="text" value="<?php echo esc_attr( $link ); ?>">
      </p>
      <?php 
   }

   public function update( $new_instance, $old_instance ) {
      $instance = array();
      $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
      $instance['link'] = ( ! empty( $new_instance['link'] ) ) ? strip_tags( $new_instance['link'] ) : '';
      return $instance;
   }

}

// register widget
function register_mcswrf_widget() {
    register_widget( 'mcswrf_widget' );
}

add_action( 'pre_get_posts', 'mcswrf_category_filter' );
add_action( 'widgets_init', 'register_mcswrf_widget' );
