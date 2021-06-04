<?php

add_action( 'wp_enqueue_scripts', 'twenty_twenty_one_child_enqueue_styles' );
function twenty_twenty_one_child_enqueue_styles() {
    wp_enqueue_style( 'twenty-twenty-one-child-style', get_stylesheet_uri(),
        array( 'twenty-twenty-one-style' ), 
        wp_get_theme()->get('Version') // this only works if you have Version in the style header
    );
}