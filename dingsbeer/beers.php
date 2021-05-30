<?php


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
    //$args['format'] = array();
    $args['A'] = array('description' => 'A', 'type' => 'number');
    $args['S'] = array('description' => 'S', 'type' => 'number');
    $args['T'] = array('description' => 'T', 'type' => 'number');
    $args['M'] = array('description' => 'M', 'type' => 'number');
    $args['O'] = array('description' => 'O', 'type' => 'number');
    //$args['review_date'] = array('description' => 'Review Date');
    //$args['notes'] = array('description' => 'Notes', 'type' => 'string');

    foreach ($args as $key => $args_of_key) {
        register_post_meta($key, $args_of_key['description'], $args_of_key);
    }
}

function dingsbeerblog_register_post_type() {

    $labels = array(
        'name' => __( 'Beers', ‘dingsbeerblog’ ),
        'singular_name' => __( 'Beer', ‘dingsbeerblog’ ),
        'add_new' => __( 'New Beer', ‘dingsbeerblog’ ),
        'add_new_item' => __( 'Add New Beer', ‘dingsbeerblog’ ),
        'edit_item' => __( 'Edit Beer', ‘dingsbeerblog’ ),
        'new_item' => __( 'New Beer', ‘dingsbeerblog’ ),
        'view_item' => __( 'View Beers', ‘dingsbeerblog’ ),
        'search_items' => __( 'Search Beers', ‘dingsbeerblog’ ),
        'not_found' =>  __( 'No Beers Found', ‘dingsbeerblog’ ),
        'not_found_in_trash' => __( 'No Beers found in Trash', ‘dingsbeerblog’ ),
       );

    
    $args = array(
    'labels' => $labels,
    'has_archive' => true,
    'public' => true,
    'hierarchical' => false,
    'supports' => array(
        'title',
        'editor',
        'excerpt',
        'custom-fields',
        'thumbnail',
        'page-attributes'
    ),
    'taxonomies' => 'category',
    'rewrite'   => array( 'slug' => 'beer' ),
    'show_in_rest' => true
    );

     register_post_type('dingsbeerblog_beer', $args);

     //dingsbeerblog_register_post_meta();

}

add_action( 'init', 'dingsbeerblog_register_post_type');
