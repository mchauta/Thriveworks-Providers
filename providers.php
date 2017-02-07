<?php
/*
Plugin Name: Providers
Plugin URI: http://thriveworks.com/
Description: a plugin created to provide provider directory functionality to thriveworks.com
Version: 1.0
Author: Matt Chauta
Author URI: http://chauta.carbonmade.com/
License: GPL2
*/
?>
<?php

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'PROVIDERS_VERSION', '1.0' );
define( 'PROVIDERS__MINIMUM_WP_VERSION', '3.7' );
define( 'PROVIDERS__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PROVIDERS_DELETE_LIMIT', 100000 );

//This plugin requires the plugin Advanced Custom Fields to work. Check if ACF is active. If not, display error.

function sample_admin_notice__success() {
    $acf_active = is_plugin_active( 'advanced-custom-fields/acf.php' );
    if ( false === $acf_active ) {
?>
    <div class= "notice notice-error">
        <p><?php
         _e( 'The Thriveworks plugin relies on the <strong>"Advanced Custom Fields"</strong> plugin to work, please activate it before continuing.', 'sample-text-domain' );
?></p>
    </div>
    <?php
    }
}

add_action( 'admin_notices', 'sample_admin_notice__success' );

//enqueue style.css
function reg_providers_styles() {
    $css_path = get_stylesheet_directory() . '/style.css';
// Example: /home/user/var/www/wordpress/wp-content/plugins/my-plugin/
    wp_enqueue_style('providers-style', '/wp-content/plugins/providers/css/style.css', array(), filemtime($css_path));
}
add_action('wp_enqueue_scripts', 'reg_providers_styles');

// Creates Custom Post Type 'Providers'
function providers_init() {
    $args = array(
        'labels' => array(
            'name' => __( 'Providers' ),
            'singular_name' => __( 'Provider' ),
            'search_items' => 'Search Providers',
        ),
        'public' => true,
        'show_ui' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        'query_var' => true,
        'menu_icon' => 'dashicons-groups',
        'exclude_from_search' => (true),
        'rewrite' => array (
            'with_front' => false,
        ),
        'supports' => array (
            'title',
            'editor',
            'revisions',
            'page-attributes',
            'thumbnail',
        )
    );
    register_post_type( 'providers', $args );
}

add_action( 'init', 'providers_init' );

function location_taxonomy_providers() {
    //create Locations taxonomy
    $loc_labels = array(
        'name' => _x( 'Locations', 'taxonomy general name' ),
        'singular_name' => _x( 'Location', 'taxonomy singular name' ),
        'search_items' => __( 'Search Locations' ),
        'all_items' => __( 'All Locations' ),
        'parent_item' => __( 'Parent Location' ),
        'parent_item_colon' => __( 'Parent Location:' ),
        'edit_item' => __( 'Edit Location' ),
        'update_item' => __( 'Update Location' ),
        'add_new_item' => __( 'Add New Location' ),
        'new_item_name' => __( 'New Location' ),
        'menu_name' => __( 'Locations' ),
    );
    $loc_args   = array(
        'labels' => $loc_labels,
        'hierarchical' => false,
        'show_admin_column' => true,
    );
    register_taxonomy( 'providers_location', 'providers', $loc_args );

    //create Employee Type taxonomy
    $type_labels = array(
        'name' => _x( 'Employee Type', 'taxonomy general name' ),
        'singular_name' => _x( 'Employee Type', 'taxonomy singular name' ),
        'search_items' => __( 'Search Locations' ),
        'all_items' => __( 'All Employee Types' ),
        'parent_item' => __( 'Parent Employee Type' ),
        'parent_item_colon' => __( 'Parent Employee Type:' ),
        'edit_item' => __( 'Edit Employee Type' ),
        'update_item' => __( 'Update Employee Type' ),
        'add_new_item' => __( 'Add New Employee Type' ),
        'new_item_name' => __( 'New Employee Type' ),
        'menu_name' => __( 'Employee Types' ),
    );

    $type_args = array(
        'labels' => $type_labels,
        'hierarchical' => false,
        'show_admin_column' => true,
        'show_ui' => false,
    );
    register_taxonomy( 'providers_type', 'providers', $type_args );
}
add_action( 'init', 'location_taxonomy_providers', 0 );



/**
 * Changes strings referencing Featured Images for a post type
 */
function change_featured_image_labels($labels) {

    $labels->featured_image        = 'Profile Image';
    $labels->set_featured_image    = 'Set Profile Image';
    $labels->remove_featured_image = 'Remove Profile Image';
    $labels->use_featured_image    = 'Use as Profile Image';

    return $labels;

} // change_featured_image_labels()

add_filter('post_type_labels_providers', 'change_featured_image_labels', 10, 1);

//Add Last Modified to columns on custom post edit page
function provider_table_head($defaults)
{
    //add columns
    $defaults['order'] = 'Order';
    $defaults['modified'] = 'Last Modified';
    return $defaults;
}

add_filter('manage_providers_posts_columns', 'provider_table_head');

//function for custom columns
function populate_custom_columns($column, $post_id) {

    // http://andrewnorcross.com/tutorials/modified-date-display/
    // popluates the modified column to provide the name of the last editor, date and time.
    if ($column == 'modified') {
        $m_orig    = get_post_field('post_modified', $post_id, 'raw');
        $m_stamp   = strtotime($m_orig);
        $modified  = date('n/j/y @ g:i a', $m_stamp);
        $modr_id   = get_post_meta($post_id, '_edit_last', true);
        $auth_id   = get_post_field('post_author', $post_id, 'raw');
        $user_id   = !empty($modr_id) ? $modr_id : $auth_id;
        $user_info = get_userdata($user_id);
        echo '<p class="mod-date">';
        echo '<em>' . $modified . '</em><br />';
        echo 'by <strong>' . $user_info->display_name . '<strong>';
        echo '</p>';
    }
    if ($column == 'order') {
        $order = get_post_field('menu_order', $post_id, 'raw');
        echo '<p>' . $order . '</p>';
    }
}

add_action('manage_providers_posts_custom_column', 'populate_custom_columns', 10, 2);

//Add Phone Number to columns on custom taxonomy edit page
function location_table_head($columns)
{
    //add columns
    $columns['phone'] = 'Telephone';
    return $columns;
}

add_filter('manage_edit-providers_location_columns', 'location_table_head');

function my_custom_taxonomy_columns_content( $content, $column_name, $term_id )
{
    if ( 'phone' == $column_name ) {
        $phone = get_field('phone_number', 'providers_location_' . $term_id );
        $content = $phone;
    }
	return $content;
}
add_filter( 'manage_providers_location_custom_column', 'my_custom_taxonomy_columns_content', 10, 3 );
/*
 * Display a custom taxonomy dropdown in admin
 * @author Mike Hemberger
 * @link http://thestizmedia.com/custom-post-type-filter-admin-custom-taxonomy/
 */
add_action('restrict_manage_posts', 'tsm_filter_post_type_by_taxonomy');
function tsm_filter_post_type_by_taxonomy() {
	global $typenow;
	$post_type = 'providers'; // change to your post type
	$taxonomy  = 'providers_location'; // change to your taxonomy
	if ($typenow == $post_type) {
		$selected      = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
		$info_taxonomy = get_taxonomy($taxonomy);
		wp_dropdown_categories(array(
			'show_option_all' => __("Show All {$info_taxonomy->label}"),
			'taxonomy'        => $taxonomy,
			'name'            => $taxonomy,
			'orderby'         => 'name',
			'selected'        => $selected,
			'show_count'      => true,
			'hide_empty'      => true,
		));
	};
}
/**
 * Filter posts by taxonomy in admin
 * @author  Mike Hemberger
 * @link http://thestizmedia.com/custom-post-type-filter-admin-custom-taxonomy/
 */
add_filter('parse_query', 'tsm_convert_id_to_term_in_query');
function tsm_convert_id_to_term_in_query($query) {
	global $pagenow;
	$post_type = 'providers'; // change to your post type
	$taxonomy  = 'providers_location'; // change to your taxonomy
	$q_vars    = &$query->query_vars;
	if ( $pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0 ) {
		$term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
		$q_vars[$taxonomy] = $term->slug;
	}
}

require_once( PROVIDERS__PLUGIN_DIR . 'shortcode.php' );

add_action( 'template_redirect', 'providers_redirect_post' );

function providers_redirect_post() {
  $queried_post_type = get_query_var('post_type');
         if (!is_user_logged_in() || !current_user_can('administrator')) {
                if ( is_single() && 'providers' == $queried_post_type) {
                    wp_redirect( home_url(), 301 );
                    exit;
            }

        }
}
