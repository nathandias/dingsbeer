<?php
#
# Add a shortcode that displays and processes a beer review search form 
#

add_shortcode('beer_review_search','dbb_beer_review_search');

# Global Variables
$post_fields = ['beer_name', 'notes']; # the custom post type title and content fields
$tax_fields = ['brewery', 'style', 'format'];
$text_fields = ['series_name'];
$numeric_fields = ['year', 'abv', 'appearance', 'smell', 'taste', 'mouthfeel', 'overall'];

function dbb_beer_review_search($atts = null) {

    global $tax_fields;
    global $text_fields;
    global $numeric_fields;

    # generate the search form
    $output .= dbb_beer_review_search_form();

    $tried = ($_POST['tried'] == 'yes');

    if ($tried) {

        if ( ! isset( $_POST['_dbb_nonce'] ) 
            || ! wp_verify_nonce( $_POST['_dbb_nonce'], 'dbb_beer_search' ) 
        ) {
            error_log("nonce error");
            print 'Sorry, your nonce did not verify.';
            exit;
        } else {
            # passed the nonce verification, proceed



            # validate the submitted form data
            if ($validation_errors = dbb_beer_search_validate_form()) {
                $output .= $validation_errors;
                return  __( $output );
            }

            # build the query
            $args = array('post_type' => 'dingsbeerblog_beer');

            $tax_query = [];
            $meta_query = [];
            $date_query = [];

            foreach ($tax_fields as $tax_field) {
                $search_term = $_POST["dbb_beer_search_${tax_field}"];
                if ($search_term != "") {
                    array_push($tax_query, array(
                        'taxonomy' => $tax_field,
                        'field' => 'name',
                        'terms' => $search_term,
                    ));
                }
            }

            foreach ($text_fields as $text_field) {
                $search_term = $_POST["dbb_beer_search_${text_field}"];
                $compare = $_POST["dbb_beer_search_${text_field}_compare"];

                error_log("processing $text_field: (search_term = '$search_term', compare = '$compare'");

                if ($search_term == "") {
                    continue;
                }

                switch ($compare) {
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
                $search_term = $_POST["dbb_beer_search_${numeric_field}"];
                $compare = $_POST["dbb_beer_search_${numeric_field}_compare"];

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
                array_push($meta_query, array( 'key' => $numeric_field, 'value' => $search_term, 'type' => 'numeric', 'compare' => $compare_operator));
            }

            $start_date = $_POST['dbb_beer_search_review_date_start'];
            $end_date = $_POST['dbb_beer_search_review_date_end'];

            error_log("start_date = $start_date");
            error_log("end_date = $end_date");


            $date_query = [];
            $date_range = [];
            if ($start_date != '') {
                $start_date = DateTime::createFromFormat('n-j-Y', $start_date)->format('Y-m-d');
                $date_range['after'] = $start_date;
            }
            if ($end_date != '') {
                $end_date = DateTime::createFromFormat('n-j-Y', $end_date)->format('Y-m-d');
                $date_range['before'] = $end_date;
            }
            if ($start_date != '' || $end_date != '') {
                $date_query = array($date_range, 'inclusive' => true);
            }

            $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
            
            # MySQL escaping notes
            #
            # meta_query, tax_query, and date_query handled directly by WP_Query, which handles escaping
            # those parts of the query
            #
            # But, we build the post_title and post_content parts of the query ourselves, so must handle
            # the escaping manually for those parts too

            $args = array(
                'post_type' => 'dingsbeerblog_beer',
                'posts_per_page' => 25,
                'paged' => $paged,
                'meta_query' => $meta_query,
                'tax_query'=> $tax_query,
                'date_query' => $date_query,
                'title_search_term' => $_POST['dbb_beer_search_beer_name'],
                'title_search_compare' => $_POST['dbb_beer_search_beer_name_compare'],
                'content_search_term' => $_POST['dbb_beer_search_notes'],
                'content_search_compare' => $_POST['dbb_beer_search_notes_compare'],
            );
            
            error_log($meta_query);

            
            // The Query
            add_filter( 'posts_where', 'dbb_beer_content_filter', 10, 2);
            add_filter( 'posts_where', 'dbb_beer_title_filter', 10, 2 );
            $the_query = new WP_Query( $args );    
            remove_filter( 'posts_where', 'dbb_beer_title_filter', 10 );
            remove_filter( 'posts_where', 'dbb_beer_content_filter', 10 );
            
            // The Loop
            if ( $the_query->have_posts() ) {
                $output .= "<div id='dbb_beer_search_results' class='dbb_beer_search_results'>\n'";
                
                $output .= '<h2>Results:</h2>';

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
                $output .= esc_html( __(
                    'Sorry, no posts matched your criteria.') );
            }
            $output .= "</div>\n";

        }

    }
    return $output;
}

//
// HTML form for the beer review search
//

function dbb_beer_review_search_form() {

    global $post_fields;
    global $tax_fields;
    global $text_fields;
    global $numeric_fields;

    $form_output = "
        <div class='dbb_beer_search_form' id='dbb_beer_search_form'>
        <form action='" . $_SERVER['REQUEST_URI'] . "' name='dbb_beer_search' method='POST'>";

    
    $form_output .= wp_nonce_field( 'dbb_beer_search', '_dbb_nonce', true, false );

    error_log("post nonce");

    $form_output .= "
        <table>
        <tbody>
        <tr><th colspan='3' id='search_by' style='text-align:left'><label for='search_by'>Search by:</label></th></tr>
    ";
    
    foreach ($post_fields as $field) {
        $form_output .= display_search_field($field, 'text');
    }

    foreach ($tax_fields as $field) {
        $form_output .= display_tax_search_field($field);
    }

    foreach ($text_fields as $field) {
        $form_output .= display_search_field($field, 'text');
    }

    foreach ($numeric_fields as $field) {
        $form_output .= display_search_field($field, 'numeric');
    }

    $form_output .= display_date_search_field('review_date');

    $form_output .= '
        </tbody>
        </table>

        <input type="hidden" name="tried" value="yes" />

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
    
    $output = "<tr><td><label for='$full_field_name' id='{$full_field_name}_label' class='form_label'>$human_field_name</label></td>
    <td colspan='2'><select id='$full_field_name' name='$full_field_name'>
    ";

    $selected = ($_POST[$full_field_name] == '') ? 'selected' : '';

    $output .= "<option value='' $selected>*show all*</option>";

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
        $selected = ($_POST[$full_field_name] == $term_name) ? 'selected' : '';
        $output .= "<option value='$term_name' $selected>$term_name</option>";
    }

    $output .= "</select></td></tr>\n";

    return $output;

}

function display_search_field($field_name, $type = 'text', $add_br = false) {

    $full_field_name = 'dbb_beer_search_' . $field_name;
    $human_field_name = humanize($field_name);
    $prev_field_value = $_POST[$full_field_name];

    $compare_name = $full_field_name . "_compare";
    $prev_compare_value = $_POST[$compare_name];

    $output = "
        <tr><td><label for='$full_field_name' id='{$full_field_name}_label' class='form_label'>$human_field_name</label></td>

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

function display_date_search_field ($field)  {

    $human_field_name = humanize($field);
    $start_date_field = 'dbb_beer_search_' . $field . '_start';
    $prev_start_date = $_POST[$start_date_field];
    $end_date_field = 'dbb_beer_search_' . $field . '_end';
    $prev_end_date = $_POST[$end_date_field];

    $output .= "<tr><td style='vertical-align:top'><label for='$start_date_field' id='{$start_date_field}_label' class='form_label'>$human_field_name</label></td>";

    $output .= "<td>From<br/><input type='text' id='$start_date_field' name='$start_date_field' value='$prev_start_date' /></td>";
    $output .= "<td>To<br/> <input type='text' id='$end_date_field' name='$end_date_field' value='$prev_end_date' /></td></tr>";

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
                $where .= ' AND ' . $wpdb->posts . $wpdb->prepare(".post_title = %s", $search_term);           
                break;
            case 'is_not':
                $where .= ' AND ' . $wpdb->posts . $wpdb->prepare(".post_title != %s", $search_term);
                break;
            }
    }
    error_log("search term: $search_term");
    error_log("where = $where");
    


    return $where;
}

function dbb_beer_content_filter($where, $wp_query) {
    global $wpdb;

    // the post content corresponds to the "Notes" field of the beer review

    error_log("called dbb_beer_content_filter");
    error_log("content_search_term: " . $wp_query->query['content_search_term']);

    if ($search_term = $wp_query->query['content_search_term']) {
        $compare = $wp_query->query['content_search_compare'];

        error_log("compare = $compare");

        switch ($compare) {
            case 'contains':
                $where .= ' AND ' . $wpdb->posts . '.post_content LIKE \'%' . $wpdb->esc_like( $search_term ) . '%\'';
                break;
            case 'is':
                $where .= ' AND ' . $wpdb->posts . '.post_content = \'' . esc_sql( $search_term ) . '\'';
                break;
            case 'is_not':
                $where .= ' AND ' . $wpdb->posts . '.post_content != \'' . esc_sql( $search_term ) . '\'';
                break;
            }
    }
    error_log("search term: $search_term");
    error_log("where = $where");
    
    return $where;
}

function dbb_beer_search_validate_form () {

    #
    # Returns a string containing html for an unordered list of validation errors
    # -or- boolean false if no errors found
    # 

    $validation_errors = [];

    $short_text_fields = array('beer_name', 'brewery_name', 'format', 'style', 'series_name');

    foreach ($short_text_fields as $text_field) {
        $actual_text_field = 'dbb_beer_search_' . $text_field;
        $value = $_POST[$actual_text_field];
        if (strlen($value) > 256) {
            array_push($validation_errors, humanize($text_field) . " is too long (must be less than 256 characters)");
        }
    }

    $numeric_fields = array('abv', 'appearance', 'taste', 'smell', 'mouthfeel', 'overall');

    foreach ($numeric_fields as $numeric_field) {
        $actual_numeric_field = 'dbb_beer_search_' . $numeric_field;
        $value = $_POST[$actual_numeric_field];

        error_log("\$numeric_field = $numeric_field, \$value = $value");
                
        if (is_numeric($value) || $value == '') {

        } else {
            array_push($validation_errors, humanize($numeric_field) . " should be a number or left blank");
        }
    }

    # the field names
    $start_date_fn = 'dbb_beer_search_review_date_start';
    $end_date_fn = 'dbb_beer_search_review_date_end';
    
    $both_dates_valid = true;
    $date_fields = array('review_date_start', 'review_date_end');
    foreach ($date_fields as $date_field) {
        $actual_date_field = 'dbb_beer_search_' . $date_field;
        $value = $_POST[$actual_date_field];

        error_log("\$date_field = $date_field, \$value = $value");


        if (validateDate($value) || $value == '') {
        } else {
            array_push($validation_errors, humanize($date_field) . " should be a date in MM-DD-YYYY format");
            $both_dates_valid = false;
        }
    }

    error_log("\$both_dates_valid == $both_dates_valid");
    # start of review date search range must occur before end, if both start and end specified
    if ($both_dates_valid && ($_POST[$start_date_fn] != '') && ($_POST[$end_date_fn] != '')) {

        $start_date = DateTime::createFromFormat('n-j-Y', $_POST[$start_date_fn]);
        $end_date = DateTime::createFromFormat('n-j-Y', $_POST[$end_date_fn]);

        error_log("\$start_date (validating) = " . $start_date->format('n-j-Y'));
        error_log("\$end_date (validating) = " . $end_date->format('n-j-Y'));
    
        if ($start_date > $end_date) {
            array_push($validation_errors, "Review date: start of date range must occur before end");
        }  
    }





    if ($validation_errors) {
        $output = "<strong style='color:red'>Invalid search terms. Please fix these problems.</strong>\n";
        $output .= "<ul>\n";
        foreach ($validation_errors as $validation_error) {
            $output .= "<li>$validation_error</li>\n";
        }
        $output .= "</ul>\n";
        return $output;
    } else {
        return false; # passed validation, no errors returned
    }
}

function validateDate($date, $format = 'n-j-Y') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}