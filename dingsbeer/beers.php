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
    'hierarchical' => false,
    'supports' => array(
        'title',
        'editor',
        'excerpt',
        'custom-fields',
        'thumbnail',
        'page-attributes'
    ),
    'taxonomies' => array('category'),
    'rewrite'   => array( 'slug' => 'beer' ),
    'show_in_rest' => true
    );

     register_post_type('dingsbeerblog_beer', $args);

     dingsbeerblog_register_post_meta();

}

add_action( 'init', 'dingsbeerblog_register_post_type');

//
// Add a shortcode that displays and processes a beer review search form 
//

add_shortcode('beer_review_search','dbb_beer_review_search');

function dbb_beer_review_search($atts = null){

    $output = dbb_beer_review_search_form();


// build the query
// do the query
// output the results

// $args = array('post_type' => 'dingsbeerblog_beer','order' => 'asc', 
//             'meta_query' => array(
//               array(
//                   'key' => 'country',
//                   'value' => $_GET['s_value'],
//                   'compare' => 'Like',
//                   )
//               )
//           );
//     $the_query = new WP_Query( $args );
//     if( $the_query->have_posts() ):
//         while( $the_query->have_posts() ) : $the_query->the_post();
        
//           if($_GET['s_value']==''){
//             //before search hide the posts 
//           }
//           else {
//             $out .= '<li>' . get_the_title() . '</li>';
//           }  
//         endwhile;
//     endif;    
    return $output;
}

//
// HTML form for the beer review search
//

function dbb_beer_review_search_form() {

    $form_output = '
        <div class="dingsbeerblog_beer_search">
        <form action="" name="dbb_beer_search" method="get">
        <label for="search_by">Search by:</label>
        <div id="search_by" name="search_by">';

    $text_fields = ['beer_name', 'brewery', 'series_name', 'style', 'note'];
    $number_fields = ['year', 'abv', 'a', 's', 't', 'm', 'o'];

    $i = 0;
    foreach ($text_fields as $text_field) {
        $form_output .= display_text_search_field($text_field, ($i % 2) == 1);
        $i++;
    }

    foreach ($number_fields as $number_field) {
        $form_output .= display_number_search_field($number_field, ($i % 2) == 1);
        $i++;
    }

    $form_output .= '
        <input id="submit" type="submit" value="Search" />
        </form></div>';
    
    return $form_output;
}

function humanize_field_name($field_name) {
    return ucwords(str_replace("_", " ", $field_name));

}

function display_text_search_field($field_name, $add_br = false) {

    $human_field_name = humanize_field_name($field_name);

    $output = "
        <label for='dbb_beer_search_${field_name}'>$human_field_name</label>
        <input id='dbb_beer_search_${field_name}' name='dbb_beer_search_${field_name}' type='text' value=''/>

        <select id='dbb_beer_search_${field_name}_comparision' name='dbb_beer_search_${field_name}_comparison'>
            <option value=''></option>
            <option value='is'>is</option>
            <option value='is_not'>is not</option>
            <option value='contains'>contains</option>
        </select>
        ";

    $output .= $add_br ? "<br/>\n" : "\n";

    return $output;

}

function display_number_search_field($field_name, $add_br = false) {
    
    $human_field_name = humanize_field_name($field_name);
    $output = "
        <label for='dbb_beer_search_${field_name}'>$human_field_name</label>
        <input id='dbb_beer_search_${field_name}' name='dbb_beer_search_${field_name}' type='text' value=''/>

        <select id='dbb_beer_search_${field_name}_comparision' name='dbb_beer_search_${field_name}_comparison'>
            <option value=''></option>
            <option value='='>=</option>
            <option value='!='>!=</option>
            <option value='&lt'>&lt;</option>
            <option value='&le;='>&le;</option>
            <option value='&gt;'>&gt;</option>
            <option value='&ge;''>&ge;</option>
        </select>
    ";

    $output .= $add_br ? "<br/>\n" : "\n";

    return $output;
}

