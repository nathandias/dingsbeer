<?php

//
// Define the beer review custom post type and related taxonomies
//

function var_error_log( $object=null ){
    ob_start();                    // start buffer capture
    var_dump( $object );           // dump the values
    $contents = ob_get_contents(); // put the buffer into a variable
    ob_end_clean();                // end capture
    error_log( $contents );        // log contents of the result of var_dump( $object )
}

function dingsbeerblog_register_post_meta () {
    
    $default_args = array(
        'object_subtype' => '',
        'type' => 'string',
        'description' => '',
        'single' => true,
        'default' => '',
        'sanitize_callback' => '',
        'auth_callback' => '',
        'show_in_rest' => true,
    );

    $args['brewery'] = array('description' => 'Brewery');
    $args['series'] = array('description' => 'Series Name');
    $args['year'] = array('description' => 'Year', 'type' => 'integer');
    $args['style'] = array('description' => 'Style');
    $args['ABV'] = array('description" => ABV', 'type' => 'number');
    $args['appearance'] = array('description' => 'Appearance', 'type' => 'number');
    $args['smell'] = array('description' => 'Smell', 'type' => 'number');
    $args['taste'] = array('description' => 'Taste', 'type' => 'number');
    $args['mouthfeel'] = array('description' => 'Mouthfeel', 'type' => 'number');
    $args['overall'] = array('description' => 'Overall', 'type' => 'number');
    //$args['review_date'] = array('description' => 'Review Date');
    //$args['notes'] = array('description' => 'Notes', 'type' => 'string');

    foreach ($args as $key => $args_of_key) {
        register_post_meta($key, $args_of_key['description'], $args_of_key);
    }
}

function dingsbeerblog_register_post_type() {

    $labels = array(
        'name' => __( 'Beers', 'dingsbeerblog' ),
        'singular_name' => __( 'Beer', 'dingsbeerblog' ),
        'add_new' => __( 'New Beer', 'dingsbeerblog' ),
        'add_new_item' => __( 'Add New Beer', 'dingsbeerblog' ),
        'edit_item' => __( 'Edit Beer', 'dingsbeerblog' ),
        'new_item' => __( 'New Beer', 'dingsbeerblog' ),
        'view_item' => __( 'View Beers', 'dingsbeerblog' ),
        'search_items' => __( 'Search Beers', 'dingsbeerblog' ),
        'not_found' =>  __( 'No Beers Found', 'dingsbeerblog' ),
        'not_found_in_trash' => __( 'No Beers found in Trash', 'dingsbeerblog' ),
       );

    
    $args = array(
    'labels' => $labels,
    'has_archive' => true,
    'public' => true,
    'exclude_from_search' => false,
    'hierarchical' => false,
    'supports' => array(
        'title',
        'editor',
        'excerpt',
        'custom-fields',
        'thumbnail',
        'page-attributes'
    ),
    'taxonomies' => array('brewery', 'style', 'format'),
    'rewrite'   => array( 'slug' => 'beer' ),
    'menu_icon' => 'dashicons-beer',
    'show_in_rest' => true
    );

     register_post_type('dingsbeerblog_beer', $args);

     dingsbeerblog_register_post_meta();

}

add_action( 'init', 'dingsbeerblog_register_post_type');




function dbb_register_taxonomy_brewery() {
    $labels = array(
        'name'              => _x( 'Breweries', 'taxonomy general name' ),
        'singular_name'     => _x( 'Brewery', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Breweries' ),
        'all_items'         => __( 'All Breweries' ),
        'parent_item'       => __( 'Parent Brewery' ),
        'parent_item_colon' => __( 'Parent Brewery:' ),
        'edit_item'         => __( 'Edit Brewery' ),
        'update_item'       => __( 'Update Brewery' ),
        'add_new_item'      => __( 'Add New Brewery' ),
        'new_item_name'     => __( 'New Brewery' ),
        'menu_name'         => __( 'Brewery' ),
    );
    $args   = array(
        'hierarchical'      => false, // make it non-hierarchical (like tags)
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'brewery' ],
        'has_archive'       => true,
    );
    register_taxonomy( 'brewery', [ 'dingsbeerblog_beer' ], $args );
}

add_action( 'init', 'dbb_register_taxonomy_brewery' );

function dbb_register_taxonomy_format() {
    $labels = array(
        'name'              => _x( 'Formats', 'taxonomy general name' ),
        'singular_name'     => _x( 'Format', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Formats' ),
        'all_items'         => __( 'All Formats' ),
        'parent_item'       => __( 'Parent Format' ),
        'parent_item_colon' => __( 'Parent Format:' ),
        'edit_item'         => __( 'Edit Format' ),
        'update_item'       => __( 'Update Format' ),
        'add_new_item'      => __( 'Add New Format' ),
        'new_item_name'     => __( 'New Format' ),
        'menu_name'         => __( 'Format' ),
    );
    $args   = array(
        'hierarchical'      => false, // make it non-hierarchical (like tags)
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'format', 'with_front' => false, 'hierarchical' => false],
        'has_archive'       => true,
    );
    register_taxonomy( 'format', [ 'dingsbeerblog_beer' ], $args );
}
add_action( 'init', 'dbb_register_taxonomy_format' );

function dbb_register_taxonomy_style() {
    $labels = array(
        'name'              => _x( 'Styles', 'taxonomy general name' ),
        'singular_name'     => _x( 'Style', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Styles' ),
        'all_items'         => __( 'All Styles' ),
        'parent_item'       => __( 'Parent Style' ),
        'parent_item_colon' => __( 'Parent Style:' ),
        'edit_item'         => __( 'Edit Style' ),
        'update_item'       => __( 'Update Style' ),
        'add_new_item'      => __( 'Add New Style' ),
        'new_item_name'     => __( 'New Style' ),
        'menu_name'         => __( 'Style' ),
    );
    $args   = array(
        'hierarchical'      => false, // make it non-hierarchical (like tags)
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'style', 'with_front' => false, 'hierarchical' => false ],
        'has_archive'       => true,
    );
    register_taxonomy( 'style', [ 'dingsbeerblog_beer' ], $args );
}
add_action( 'init', 'dbb_register_taxonomy_style' );

add_filter('use_classic_editor_for_beer_review_cpt', 'prefix_disable_gutenberg', 10, 2);
function prefix_disable_gutenberg($current_status, $post_type)
{
    // Use your post type key instead of 'product'
    if ($post_type === 'dingsbeerblog_beer_review') return false;
    return $current_status;
}




