<?php
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
    $numeric_fields = ['year', 'abv', 'appearance', 'smell', 'taste', 'mouthfeel', 'overall'];

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
        <form action="" name="dbb_beer_search" method="get">';

    $text_fields = ['beer_name', 'brewery', 'series_name', 'style', 'format', 'note'];
    $numeric_fields = ['year', 'abv', 'appearance', 'smell', 'taste', 'mouthfeel', 'overall'];

    $form_output .= "
        <table>
        <tbody>
        <t><th colspan='3' id='search_by' style='text-align:left'><label for='search_by'>Search by:</label></th></tr>
    ";

    foreach ($text_fields as $field) {
        $form_output .= display_search_field($field, 'text');
    }

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