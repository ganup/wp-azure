<?php
/*
Plugin Name:Woo Custom Tabs
Plugin URI:
Description: A plugin to add tabs for Product in WooCommerce
Version: 1.0
Author: Ganesh Paygude

/* Setup the plugin. */
add_action( 'plugins_loaded', 'woo_tab_plugin_setup' );

/* Register plugin activation hook. */
register_activation_hook( __FILE__, 'woo_tab_plugin_activation' );

/* Register plugin activation hook. */
register_deactivation_hook( __FILE__, 'woo_tab_plugin_deactivation' );
/**
 * Do things on plugin activation.
 *
 */
function woo_tab_plugin_activation() {
	/* Flush permalinks. */
    flush_rewrite_rules();
	
	/* Register post type. */
	  add_action( 'init','woo_tabs_post_type', 0 );
}
/**
 * Flush permalinks on plugin deactivation.
 */
function woo_tab_plugin_deactivation() {
    flush_rewrite_rules();
}
function woo_tab_plugin_setup() {

/* Get the plugin directory URI. */
define( 'WOO_TAB_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );

add_action( 'init','woo_tabs_post_type', 0 );

/* Add meta boxes on the 'add_meta_boxes' hook. */
add_action( 'add_meta_boxes', 'woo_tabs_meta_boxes' );

/* Save post meta on the 'save_post' hook. */
add_action( 'save_post', 'woo_tabs_save_meta', 10, 2 );
  
//add tabs to product page
if (!is_admin()){
	add_filter( 'woocommerce_product_tabs','woo_product_tabs' );
}
}
function woo_tabs_post_type(){
 $labels = array(
            'name'                => _x( 'Woo Tabs', 'Post Type General Name', 'Woo_tab' ),
            'singular_name'       => _x( 'Woo Tab', 'Post Type Singular Name', 'Woo_tab' ),
            'menu_name'           => __( 'Woo Tabs', 'Woo_tab' ),
            'parent_item_colon'   => __( '', 'Woo_tab' ),
            'all_items'           => __( 'Woo Tabs', 'Woo_tab' ),
            'view_item'           => __( '', 'Woo_tab' ),
            'add_new_item'        => __( 'Add Woo Tab', 'Woo_tab' ),
            'add_new'             => __( 'Add New', 'Woo_tab' ),
            'edit_item'           => __( 'Edit Woo Tab', 'Woo_tab' ),
            'update_item'         => __( 'Update Woo Tab', 'Woo_tab' ),
            'search_items'        => __( 'Search Woo Tab', 'Woo_tab' ),
            'not_found'           => __( 'Not found', 'Woo_tab' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'Woo_tab' ),
        );
        $args = array(
            'label'               => __( 'Woo Tabs', 'Woo_tab' ),
            'description'         => __( 'Custom WooCommerce Tabs', 'Woo_tab' ),
            'labels'              => $labels,
            'supports'            => array( 'title', 'editor', 'custom-fields' ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => false,
            'show_in_admin_bar'   => true,
            'menu_position'       => 25,
            'menu_icon'           => 'dashicons-networking',
            'can_export'          => true,
            'has_archive'         => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'capability_type'     => 'post',
        );
        register_post_type( 'Woo-tab', $args );	
}
function woo_tabs_meta_boxes() {

  add_meta_box(
    'woo-tabs-meta',      // Unique ID
    esc_html__( 'Woo Tabs', 'example' ),    // Title
    'woo_tabs_meta_box',   // Callback function
    'product',         // Admin page (or post type)
    'side',         // Context
    'default'         // Priority
  );
}
function woo_tabs_meta_box( $object, $box ) { ?>
  
  <p>
  <select  name="woo-custom-tab[]"  multiple="multiple" >
        <?php   
              $tabs_ids = get_post_meta( $object->ID, 'woo_custom_tabs', true );
			  $woo_tab_ids = ! empty( $tabs_ids ) ?  $tabs_ids : array();
              foreach ( woo_tabs_list() as $id => $label ) {
				$selected = in_array($id, $woo_tab_ids)?  'selected="selected"' : '';
                echo '<option value="' . esc_attr( $id ) . '"'.$selected.'>' . esc_html( $label ) . '</option>';
             }
        ?>
  </select>
  </p>
<?php }

/* Save the meta box's post metadata. */
function woo_tabs_save_meta( $post_id, $post ) {


  /* Get the post type object. */
  $post_type = get_post_type_object( $post->post_type );

  /* Check if the current user has permission to edit the post. */
  if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
    return $post_id;

  /* Get the posted data and sanitize it for use as an HTML class. */
  $new_meta_value = ( isset( $_POST['woo-custom-tab'] ) ? sanitize_html_class( $_POST['woo-custom-tab'] ) : '' );

  /* Get the meta key. */
  $meta_key = 'woo_custom_tabs';

  /* Get the meta value of the custom field key. */
  $meta_value = get_post_meta( $post_id, $meta_key, true );

  /* If a new meta value was added and there was no previous value, add it. */
  if ( $new_meta_value && '' == $meta_value )
    add_post_meta( $post_id, $meta_key, $new_meta_value, true );

  /* If the new meta value does not match the old value, update it. */
  elseif ( $new_meta_value && $new_meta_value != $meta_value )
    update_post_meta( $post_id, $meta_key, $new_meta_value );

  /* If there is no new meta value but an old value exists, delete it. */
  elseif ( '' == $new_meta_value && $meta_value )
    delete_post_meta( $post_id, $meta_key, $meta_value );
}

 /**
 * Woo Tabs_list
 */
    function woo_tabs_list(){
        $args = array(
            'post_type'      => 'Woo-tab',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids'
        );
        $woo_tabs_arr = array();
        $posts = get_posts($args);
        if ( $posts ){
			foreach ( $posts as $post_id ) {
            $woo_tabs_arr[ $post_id ] = get_the_title($post_id);
        }
        return $woo_tabs_arr;
		}
	}
/**
* woo_product_tabs
*/
    function woo_product_tabs($tabs){
		
        global $post;        
        $woo_tabs = get_post_meta( $post->ID, 'woo_custom_tabs', true );
        $woo_tab_ids = ! empty($woo_tabs  ) ? $woo_tabs : null;
 
        if ($woo_tab_ids){           
            foreach ($woo_tab_ids as $id) {       	
					
	                $tabs['woo_tab_'.$id] = array(
	                    'title'    => get_the_title($id),
	                    'priority' =>  50 ,
	                    'callback' => 'add_woo_tab',
	                    'content'  => apply_filters('the_content',get_post_field( 'post_content', $id)) 
	                );          	
            }
        }
        return $tabs;
    } 
/**
 * ADD_tab     
*/
function add_woo_tab($key,$tab){
        global $post;
        echo '<h2>'.apply_filters('woo_tab_title',$tab['title'],$tab,$key).'</h2>';
        echo apply_filters('woo_tab_content',$tab['content'],$tab,$key);
}