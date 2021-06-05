<?php

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

function dbb_beer_review_search($atts = null) {

    $output = dbb_beer_review_search_form();

    $output .= '<h2>Results:</h2>';

    // build the query
    $args = array('post_type' => 'dingsbeerblog_beer');

    // need to search post_title (beer) & post_content (notes)
    // HMMMM......

    // filter results on post meta fields

    $text_fields = ['brewery', 'series_name', 'style', 'format'];
    $numeric_fields = ['year', 'abv', 'a', 's', 't', 'm', 'o'];

    $meta_query = [];
    foreach ($text_fields as $text_field) {
        $search_term = $_GET["dbb_beer_search_${text_field}"];
        $compare = $_GET["dbb_beer_search_${text_field}_compare"];

        error_log("processing $text_field: (search_term = '$search_term', compare = '$compare'");

        switch ($compare) {
            case '':
                continue 2; // skip to next field if this field's comapre type is blank
            case 'is':
                $compare_operator = '=';
                break;
            case 'is_not':
                $compare_operator = '!=';
                break;
            case 'contains':
                $compare_operator = "LIKE";
                break;
            default:
                die("invalid comparison operator for field $text_field");
        }
        array_push($meta_query, array( 'key' => $text_field, 'value' => $search_term, 'compare' => $compare_operator));
    }

    foreach ($numeric_fields as $numeric_field) {
        $search_term = $_GET["dbb_beer_search_${numeric_field}"];
        $compare = $_GET["dbb_beer_search_${numeric_field}_compare"];
        switch ($compare) {
            case '':
                continue 2; // skip to next field if this field's compare type is blank
            case 'equals':
                $compare_operator = '=';
                break;
            case 'does_not_equal':
                $compare_operator = '!=';
                break;
            case 'less_than':
                $compare_operator = '<';
                break;
            case 'less_than_or_equal':
                $compare_operator = '<=';
                break;
            case 'greater_than':
                $compare_operator = '>';
                break;
            case 'greater_than_or_equal':
                $compare_operator = '>=';
                break;
            default:
                die("invalid comparison operator for field $numeric_field");
        }
        array_push($meta_query, array( 'key' => $numeric_field, 'value' => $search_term, 'compare' => $compare_operator));
    }


    $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
    
    $args = array(
        'post_type' => 'dingsbeerblog_beer',
        'posts_per_page' => 25,
        'paged' => $paged,
        'meta_query' => $meta_query,
    );
    
    var_error_log($meta_query);

    
    // The Query
    $the_query = new WP_Query( $args );
    
    // The Loop
    if ( $the_query->have_posts() ) {
        $output .= '<ul>';
        while ( $the_query->have_posts() ) {
            $the_query->the_post();
            
            
            $output .= '<li><a href="' . get_permalink($post->ID) . '">' . get_the_title() . '</a></li>';
        }
        $output .= '</ul>';

        $output .= '<div class="pagination">';

        $output .= paginate_links( array(
            'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
            'total'        => $the_query->max_num_pages,
            'current'      => max( 1, get_query_var( 'paged' ) ),
            'format'       => '?paged=%#%',
            'show_all'     => false,
            'type'         => 'plain',
            'end_size'     => 2,
            'mid_size'     => 1,
            'prev_next'    => true,
            'prev_text'    => sprintf( '<i></i> %1$s', __( 'Previous', 'text-domain' ) ),
            'next_text'    => sprintf( '%1$s <i></i>', __( 'Next', 'text-domain' ) ),
            'add_args'     => false,
            'add_fragment' => '',
        ) );

        // $output .= '</div>\n';

        wp_reset_postdata();
    } else {
        $output .= _e( 'Sorry, no posts matched your criteria.' );
    }
  
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

    $text_fields = ['beer_name', 'brewery', 'series_name', 'style', 'format', 'note'];
    $numeric_fields = ['year', 'abv', 'a', 's', 't', 'm', 'o'];

    $i = 0; // counter to help display two fields per line by adding <br/> to even numbered fields
    
    foreach ($text_fields as $field) {
        $form_output .= display_search_field($field, 'text') . "<br/>";
    }

    foreach ($numeric_fields as $field) {
        $form_output .= display_search_field($field, 'numeric') . "<br/>";
    }
    $form_output .= '
        <input id="submit" type="submit" value="Search" />
        </form></div>';
    
    return $form_output;
}

function humanize($text) {
    return ucwords(str_replace("_", " ", $text));
}

function display_search_field($field_name, $type = 'text', $add_br = false) {

    $full_field_name = 'dbb_beer_search_' . $field_name;
    $human_field_name = humanize($field_name);
    $prev_field_value = $_GET[$full_field_name];

    $compare_name = $full_field_name . "_compare";
    $prev_compare_value = $_GET[$compare_name];

    $output = "
        <label for='$full_field_name'>$human_field_name</label>
        <input id='$full_field_name' name='$full_field_name' type='text' value='$prev_field_value'/>

        <select id='$compare_name' name='$compare_name'>
    ";

    $options = ['', 'is', 'is_not', 'contains']; // default text field comparison options
    if ($type == 'numeric') {
        $options = ['', 'equals', 'does_not_equal', 'less_than', 'less_than_or_equal', 'greater_than', 'greater_than_or_equal'];
    }

    foreach ($options as $option) {
        $human_option = humanize($option);
        $selected = ($option === $prev_compare_value) ? 'selected' : '';
        
        $output .= "<option value='$option' $selected>$human_option</option>";
    }
    
    $output .= "</select>";

    $output .= $add_br ? "<br/>\n" : "\n";

    return $output;

}



