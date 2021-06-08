<?php
//
// Add a shortcode that displays and processes a beer review search form 
//

add_shortcode('beer_review_search','dbb_beer_review_search');


// Global Variables
$tax_fields = ['brewery', 'style', 'format'];
$text_fields = ['beer_name', 'series_name', 'note'];
$numeric_fields = ['year', 'abv', 'appearance', 'smell', 'taste', 'mouthfeel', 'overall'];

function dbb_beer_review_search($atts = null) {

    global $tax_fields;
    global $text_fields;
    global $numeric_fields;

    $output = dbb_beer_review_search_form();

    $output .= '<h2>Results:</h2>';

    // build the query
    $args = array('post_type' => 'dingsbeerblog_beer');

    $tax_query = [];
    $meta_query = [];

    foreach ($tax_fields as $tax_field) {
        $search_term = $_GET["dbb_beer_search_${tax_field}"];
        if ($search_term != "") {
            array_push($tax_query, array(
                'taxonomy' => $tax_field,
                'field' => 'name',
                'terms' => $search_term,
            ));
        }
    }

    // foreach ($text_fields as $text_field) {
    //     $search_term = $_GET["dbb_beer_search_${text_field}"];
    //     $compare = $_GET["dbb_beer_search_${text_field}_compare"];

    //     error_log("processing $text_field: (search_term = '$search_term', compare = '$compare'");

    //     if ($search_term == "") {
    //         continue;
    //     }

    //     switch ($compare) {
    //         case 'is':
    //             $compare_operator = '=';
    //             break;
    //         case 'is_not':
    //             $compare_operator = '!=';
    //             break;
    //         case 'contains':
    //             $compare_operator = "LIKE";
    //             break;
    //         default:
    //             die("invalid comparison operator for field $text_field");
    //     }
    //     array_push($meta_query, array( 'key' => $text_field, 'value' => $search_term, 'compare' => $compare_operator));
    // }

    foreach ($numeric_fields as $numeric_field) {
        $search_term = $_GET["dbb_beer_search_${numeric_field}"];
        $compare = $_GET["dbb_beer_search_${numeric_field}_compare"];

        if ($search_term == "") {
            continue;
        }
        switch ($compare) {
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
        'tax_query'=> $tax_query,
        'title_search_term' => $_GET['dbb_beer_search_beer_name'],
        'title_search_compare' => $_GET['dbb_beer_search_beer_name_compare'],
    );
    
    var_error_log($meta_query);

    
    // The Query
    add_filter( 'posts_where', 'dbb_beer_title_filter', 10, 2 );
    $the_query = new WP_Query( $args );    
    remove_filter( 'posts_where', 'dbb_beer_title_filter', 10 );
    
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

    global $tax_fields;
    global $text_fields;
    global $numeric_fields;

    $form_output = '
        <div class="dingsbeerblog_beer_search">
        <form action="" name="dbb_beer_search" method="get">';

    $form_output .= "
        <table>
        <tbody>
        <t><th colspan='3' id='search_by' style='text-align:left'><label for='search_by'>Search by:</label></th></tr>
    ";
    
    $form_output .= display_search_field('beer_name', 'text');

    foreach ($tax_fields as $field) {
        $form_output .= display_tax_search_field($field);
    }

    // foreach ($text_fields as $field) {
    //     $form_output .= display_search_field($field, 'text');
    // }

    foreach ($numeric_fields as $field) {
        $form_output .= display_search_field($field, 'numeric');
    }

    $form_output .= '
        </tbody>
        </table>

        <input id="submit" type="submit" value="Search" />

        </form></div>';
    
    return $form_output;
}

function humanize($text) {
    return ucwords(str_replace("_", " ", $text));
}

function display_tax_search_field($tax_name) {
    $full_field_name = 'dbb_beer_search_' . $tax_name;
    $human_field_name = humanize($tax_name);
    
    $output = "<tr><td><label for='$full_field_name'>$human_field_name</label></td>
    <td colspan='2'><select id='$full_field_name' name='$full_field_name'>
    ";

    $selected = ($_GET[$full_field_name] == '') ? 'selected' : '';

    $output .= "<option value='' $selected><em>*show all*</em></option>";

    $args = array(
        'taxonomy'               => $tax_name,
        'fields'                 => 'all',
        'orderby'                => 'name',
        'order'                  => 'ASC',
        'hide_empty'             => false,
    );
    $the_query = new WP_Term_Query($args);
    foreach($the_query->get_terms() as $term){ 
        $term_name = $term->name;
        $selected = ($_GET[$full_field_name] == $term_name) ? 'selected' : '';
        $output .= "<option value='$term_name' $selected>$term_name</option>";
    }

    $output .= "</select></td></tr>\n";

    return $output;

}

function display_search_field($field_name, $type = 'text', $add_br = false) {

    $full_field_name = 'dbb_beer_search_' . $field_name;
    $human_field_name = humanize($field_name);
    $prev_field_value = $_GET[$full_field_name];

    $compare_name = $full_field_name . "_compare";
    $prev_compare_value = $_GET[$compare_name];

    $output = "
        <tr><td><label for='$full_field_name'>$human_field_name</label></td>

        <td><select id='$compare_name' name='$compare_name'>
    ";

    $options = ['contains', 'is', 'is_not']; // default text field comparison options
    if ($type == 'numeric') {
        $options = ['equals', 'does_not_equal', 'less_than', 'less_than_or_equal', 'greater_than', 'greater_than_or_equal'];
    }

    foreach ($options as $option) {
        $human_option = humanize($option);
        $selected = ($option === $prev_compare_value) ? 'selected' : '';
        
        $output .= "<option value='$option' $selected>$human_option</option>";
    }
    
    $output .= "</select></td>";

    $output .= "<td><input id='$full_field_name' name='$full_field_name' type='text' value='$prev_field_value'/></td>";

    $output .= "</tr>\n";

    return $output;

}


function dbb_beer_title_filter($where, $wp_query) {
    global $wpdb;

    error_log("called dbb_beer_title_filter");
    error_log("title_search_term: " . $wp_query->query['title_search_term']);

    if ($search_term = $wp_query->query['title_search_term']) {
        $compare = $wp_query->query['title_search_compare'];

        error_log("compare = $compare");

        switch ($compare) {
            case 'contains':
                $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . $wpdb->esc_like( $search_term ) . '%\'';
                break;
            case 'is':
                $where .= ' AND ' . $wpdb->posts . '.post_title = \'' . esc_sql( $search_term ) . '\'';
                break;
            case 'is_not':
                $where .= ' AND ' . $wpdb->posts . '.post_title != \'' . esc_sql( $search_term ) . '\'';
                break;
            }
    }
    error_log("search term: $search_term");
    error_log("where = $where");
    


    return $where;
}