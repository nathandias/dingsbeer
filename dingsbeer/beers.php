<?php

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
    ‘show_in_rest’ => true
    );

     register_post_type('dingsbeerblog_beer', $args);

}

add_action( 'init', 'dingsbeerblog_register_post_type');