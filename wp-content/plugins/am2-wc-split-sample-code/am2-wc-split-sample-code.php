<?php
/**
 * Plugin Name: AM2 WC Split 2016 Sample Code
 * Plugin URI: https://bitbucket.org/am2studio/split2016
 * Description: Sample code for WordCamp Split 2016 talk
 * Author: Andrej Šimunaj, AM2 Studio
 * Version: 0.9
 * Author URI: http://am2studio.hr
 */
// CUSTOM CODE //
function am2_create_endpoints( $wp_rewrite ) {
 
	$feed_rules = array(
	    'my-account/?$' 							=> 'index.php?account-page=true',
	    'my-account/edit-profile/?$' 				=> 'index.php?account-edit-profile=true',
	    '(.+?)/talks/(.+?)/?$' 						=> 'index.php?post_type=talk&city=$matches[1]&type=$matches[2]',
	    '(.+?)/talks/(.+?)/page/?([0-9]{1,})/?$' 	=> 'index.php?post_type=talk&city=$matches[1]&type=$matches[2]',
	    'index.asp?$'  								=> 'index.php?old_site_get_variables=true',
	);
 
	$wp_rewrite->rules = $feed_rules + $wp_rewrite->rules;
	return $wp_rewrite->rules;
 
}
add_filter( 'generate_rewrite_rules', 'am2_create_endpoints' );

function am2_register_query_vars( $vars ) {
	    
	$vars[] = 'account-page';
	$vars[] = 'account-edit-profile';
	$vars[] = 'old_site_get_variables';
 
	return $vars;
}
add_filter( 'query_vars', 'am2_register_query_vars' );

function am2_map_templates( $template ) {
 
	global $wp_query;
 
	if (isset($wp_query->query['account-page'])) {
	    return plugin_dir_path( __FILE__ ) . '/partials/account.php';
	}
	if (isset($wp_query->query['account-edit-profile'])) {
	    add_filter( 'pre_get_document_title', function(){ return 'Edit Profile - '.get_bloginfo( 'name', 'display' ); }, 50 );
	    return plugin_dir_path( __FILE__ ) . '/partials/account-edit-profile.php';
	}
	    
	return $template;
 
}
add_filter( 'template_include', 'am2_map_templates' );

add_action( 'init', 'am2_cpt' );
function am2_cpt() {

	$labels = array(
		'name'               => _x( 'Talks', 'post type general name' ),
		'singular_name'      => _x( 'Talk', 'post type singular name' ),
		'add_new'            => _x( 'Add New', 'Talk' ),
		'add_new_item'       => __( 'Add New Talk' ),
		'edit_item'          => __( 'Edit Talk' ),
		'new_item'           => __( 'New Talk' ),
		'view_item'          => __( 'View Talk' ),
		'search_items'       => __( 'Search Talks' ),
		'not_found'          => __( 'No Talks found' ),
		'not_found_in_trash' => __( 'No Talks found in the trash' ),
		'parent_item_colon'  => '',
		'show_in_nav_menus'  => true
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'query_var'          => true,
		'has_archive'        => true,
		'rewrite'            => array( 'slug' => 'talks' ),
		'capability_type'    => 'post',
		'hierarchical'       => true,
		'menu_position'      => null,
		'supports'           => array( 'title', 'editor', 'author', 'excerpt', 'thumbnail' ),
		'taxonomies'         => array('category','type')
	);

	register_post_type( 'talk', $args );

}

add_action( 'init', 'am2_create_taxonomies' );
function am2_create_taxonomies() {

	register_taxonomy(
        'type',
        'talk',
        array(
            'label' => __( 'Type' ),
            'rewrite'      => array( 'slug' => 'type' ),
			'capabilities' => array(),
			'hierarchical' => false,
        )
    );

    register_taxonomy(
        'city',
        'talk',
        array(
            'label' => __( 'City' ),
            'rewrite'      => array( 'slug' => 'city' ),
			'capabilities' => array(),
			'hierarchical' => false,
        )
    );

}

function am2_template_redirects( $vars ) {
	    
	global $wp_query;
	$old_site_get_variables = get_query_var('old_site_get_variables'); 

    if (!empty($old_site_get_variables)) {

		$city = $_GET['city'];
		$type = $_GET['type'];

		if(!empty($city) && !empty($type)){
			
			$new_url = get_bloginfo('url') . '/' . $city . '/talks/' . $type;
			wp_redirect($new_url, '301'); 
			die;

		}
			 
    	//404 if not found
		$wp_query->set_404();
		status_header(404);

	}
}
add_filter( 'template_redirect', 'am2_template_redirects' );