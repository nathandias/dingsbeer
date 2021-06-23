<?php
#
# Add a shortcode that displays and processes a beer review search form 
#

add_shortcode('beer_review_search','dbb_beer_search');

# Global Variables
$post_fields = ['beer_name', 'notes']; # the custom post type title and content fields
$tax_fields = ['brewery', 'style', 'format'];
$text_fields = ['series_name'];
$numeric_fields = ['year', 'abv', 'appearance', 'smell', 'taste', 'mouthfeel', 'overall'];

$dbb_prefix = 'dbb_';

function dbb_beer_search($atts = null) {

    global $dbb_prefix;

    global $tax_fields;
    global $text_fields;
    global $numeric_fields;

    # generate the search form
    $output .= dbb_search_form();

    $tried = ($_GET['tried'] == 'yes');

    if ($tried) {

        if ( ! isset( $_GET['_dbb_nonce'] ) 
            || ! wp_verify_nonce( $_GET['_dbb_nonce'], 'dbb_beer_search' ) 
        ) {
            // error_log("nonce error");
            print 'Sorry, your nonce did not verify.';
            exit;
        } else {
            # passed the nonce verification, proceed

            # validate the submitted form data
            if ($validation_errors = dbb_validate_form()) {
                $output .= $validation_errors;
                return  __( $output );
            }

            # build the query
            $args = array('post_type' => 'dingsbeerblog_beer');

            $tax_query = [];
            $meta_query = [];
            $date_query = [];

            foreach ($tax_fields as $tax_field) {
                $search_term = $_GET[$dbb_prefix . $tax_field];
                if ($search_term != "") {
                    array_push($tax_query, array(
                        'taxonomy' => $tax_field,
                        'field' => 'name',
                        'terms' => $search_term,
                    ));
                }
            }

            foreach ($text_fields as $text_field) {
                $search_term = $_GET[$dbb_prefix . $text_field];
                $compare = $_GET[$dbb_prefix . "{$text_field}_compare"];

                // error_log("processing $text_field: (search_term = '$search_term', compare = '$compare'");

                if ($search_term == "") {
                    continue;
                }

                $compare_operator = convert_compare_operator($compare, 'text');
                array_push($meta_query, array(
                    'key' => $text_field,
                    'value' => $search_term,
                    'compare' => $compare_operator
                ));
            }

            foreach ($numeric_fields as $numeric_field) {
                $search_term = $_GET[$dbb_prefix . $numeric_field];
                $compare = $_GET[$dbb_prefix . "{$numeric_field}_compare"];

                if ($search_term == "") {
                    continue;
                }

                $compare_operator = convert_compare_operator($compare, 'numeric');
                array_push($meta_query, array(
                    'key' => $numeric_field,
                    'value' => $search_term,
                    'type' => 'numeric',
                    'compare' => $compare_operator
                ));
            }


            # by this point, the dates have already been validated, so safe to convert non-'' values
            # to Y-m-d format

            $start_date = $_GET[$dbb_prefix . "review_date_start"];
            $end_date = $_GET[$dbb_prefix . "review_date_end"];

            $date_query = [];
            $date_range = [];
            if ($start_date != '') {
                $start_date = validateDate($start_date)->format('Y-m-d');
                $date_range['after'] = $start_date;
            }
            if ($end_date != '') {
                $end_date = validateDate($end_date)->format('Y-m-d');
                $date_range['before'] = $end_date;
            }
            if ($start_date != '' || $end_date != '') {
                $date_query = array($date_range, 'inclusive' => true);
            }

            $paged = ( get_query_var( 'paged', 1 ) );
            
            # MySQL escaping notes
            #
            # meta_query, tax_query, and date_query handled directly by WP_Query, which handles escaping
            # those parts of the query
            #
            # But, we build the post_title and post_content parts of the query ourselves, so must handle
            # the escaping manually for those parts too

            $title_search_term = $_GET[$dbb_prefix . 'beer_name'];
            $title_search_compare = $_GET[$dbb_prefix . 'beer_name_compare'];

            // error_log("looking for GET value: : {$dbb_prefix}beer_name");
            // error_log("GET array value = " . $_GET[$dbb_prefix . 'beer_name']);

            // error_log("title_search_(outside): $title_search");
            // error_log("title_search_compare(outside): $title_search_compare");

            $args = array(
                'post_type' => 'dingsbeerblog_beer',
                'posts_per_page' => 25,
                'paged' => $paged,
                'meta_query' => $meta_query,
                'tax_query'=> $tax_query,
                'date_query' => $date_query,
                'title_search_term' => $title_search_term,
                'title_search_compare' => $title_search_compare,
                'content_search_term' => $_GET[$dbb_prefix . "notes"],
                'content_search_compare' => $_GET[$dbb_prefix . "notes"],
            );
            
         
            // The Query
            // add_filter( 'posts_where', 'dbb_beer_content_filter', 10, 2);
            add_filter( 'posts_where', 'dbb_beer_title_filter', 10, 2 );
            $the_query = new WP_Query( $args );    
            remove_filter( 'posts_where', 'dbb_beer_title_filter', 10 );
            // remove_filter( 'posts_where', 'dbb_beer_content_filter', 10 );
            
            // The Loop
            if ( $the_query->have_posts() ) {
                $output .= "<div id='dbb_search_results' class='dbb_search_results'>\n'";
                
                $output .= '<h2>Results:</h2>';

                $output .= '<ul>';
                while ( $the_query->have_posts() ) {
                    $the_query->the_post();
                    
                    $output .= '<li><a href="' . get_permalink($post->ID) . '">' . get_the_title() . '</a></li>';
                }
                $output .= '</ul>';

                $output .= '<div class="pagination">';


                $big = 999999999; // need an unlikely integer
                $output .= paginate_links(
                    array(
                        'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                        'format' => '?paged=%#%',
                        'current' => max(
                            1,
                            get_query_var('paged')
                        ),
                        'total' => $the_query->max_num_pages //$q is your custom query
                    )
                );
              
                $output .= "</div>\n";

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

function dbb_search_form() {

    global $post_fields;
    global $tax_fields;
    global $text_fields;
    global $numeric_fields;

    wp_enqueue_style('dbb_style', '/wp-content/plugins/dingsbeer/css/style.css');

    $_SERVER['REQUEST_URI'] = remove_query_arg( '_wp_http_referer', $_SERVER['REQUEST_URI'] );
    $_SERVER['REQUEST_URI'] = preg_replace('/page\/(\d+)\//', '', $_SERVER['REQUEST_URI']);

    $form_output = "
        <div class='dbb_search_form' id='dbb_search_form'>
        <form action='" . $_SERVER['REQUEST_URI'] . "' name='dbb_beer_search' method='GET'>";

    
    $form_output .= wp_nonce_field( 'dbb_beer_search', '_dbb_nonce', true, false );
    
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

    $form_output .= <<< HTML
        <input type="hidden" name="tried" value="yes" />
        <input id="submit" type="submit" value="Search" />
        </form></div>
    HTML;

    return $form_output;
}

function humanize($text) {
    return ucwords(str_replace("_", " ", $text));
}

function display_tax_search_field($tax_name) {

    global $dbb_prefix;

    $full_field_name = $dbb_prefix . $tax_name;
    $human_field_name = humanize($tax_name);

    $output .= <<<HTML
        <div class="row">
            <div class='col-1'>
                <label for='{$full_field_name}' id='{$full_field_name}_label' class='form_label'>{$human_field_name}</label>
            </div>
            <div class='col-2-3'>
                <select id='{$full_field_name}' name='{$full_field_name}'>
    HTML;

    $selected = ($_GET[$full_field_name] == '') ? 'selected' : '';

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
        $selected = ($_GET[$full_field_name] == $term_name) ? 'selected' : '';
        $output .= "<option value='$term_name' $selected>$term_name</option>";
    }

    $output .= <<<HTML
                </select>
            </div>
        </div>
    HTML;

    return $output;

}

function display_search_field($field_name, $type = 'text', $add_br = false) {

    global $dbb_prefix;

    $full_field_name = $dbb_prefix . $field_name;
    $human_field_name = humanize($field_name);
    $prev_field_value = $_GET[$full_field_name];

    $compare_name = $full_field_name . "_compare";
    $prev_compare_value = $_GET[$compare_name];

    $output .= <<<HTML
        <div class="row">
            <div class="col-1">
                <label for='{$full_field_name}' id='{$full_field_name_label}' class='form_label'>{$human_field_name}</label>
            </div>
            <div class="col-2">
                <select name='{$compare_name}' id='{$compare_name}'>
    HTML;

    // insert the options
    $options = ['contains', 'is', 'is_not']; // default text field comparison options
    if ($type == 'numeric') {
        $options = ['equals', 'does_not_equal', 'less_than', 'less_than_or_equal', 'greater_than', 'greater_than_or_equal'];
    }

    foreach ($options as $option) {
        $human_option = humanize($option);
        $selected = ($option === $prev_compare_value) ? 'selected' : '';
        
        $output .= "<option value='$option' $selected>$human_option</option>\n";
    }

    $output .= <<<HTML
                </select>
            </div>
            <div class="col-3">
                <input type="text" id='{$full_field_name}' name='{$full_field_name}' value='{$prev_field_value}'>
            </div>
        </div>
    HTML;

    return $output;

}

function display_date_search_field ($field)  {

    global $dbb_prefix;

    $date_placeholder = 'MM-DD-YYYY';

    $human_field_name = humanize($field);
    
    $start_date_field = $dbb_prefix . "{$field}_start";    
    $end_date_field = $dbb_prefix . "{$field}_end";
    
    $prev_start_date = $_GET[$start_date_field];
    $prev_end_date = $_GET[$end_date_field];

    $output .= <<<HTML
        <div class="row">
            <div class='col-1'>
                <label for='{$start_date_field}' id='{$start_date_field}_label' class='form_label'>{$human_field_name}</label>
            </div>
            <div class='col-2-split'>
                From<br/>
                <input type='text' id='{$start_date_field}' name='{$start_date_field}' value='{$prev_start_date}' placeholder='{$date_placeholder}'/>
            </div>
            <div class='col-2-split'>
                To<br/>
                <input type='text' id='$end_date_field' name='$end_date_field' value='$prev_end_date' placeholder='{$date_placeholder}'/>
            </div>
        </div>
    HTML;

    return $output;

}


function dbb_beer_title_filter($where, $wp_query) {
    global $wpdb;

    if ($search_term = $wp_query->query['title_search_term']) {
        $compare = $wp_query->query['title_search_compare'];

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

    return $where;
}

function dbb_beer_content_filter($where, $wp_query) {
    global $wpdb;

    // the post content corresponds to the "Notes" field of the beer review

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
    
    return $where;
}

function dbb_validate_form () {

    #
    # Returns a string containing html for an unordered list of validation errors
    # -or- boolean false if no errors found
    # 

    global $dbb_prefix;

    $validation_errors = [];

    $short_text_fields = array('beer_name', 'brewery_name', 'format', 'style', 'series_name');

    foreach ($short_text_fields as $text_field) {
        $actual_text_field = $dbb_prefix . $text_field;
        $value = $_GET[$actual_text_field];
        if (strlen($value) > 256) {
            array_push($validation_errors, humanize($text_field) . " is too long (must be less than 256 characters)");
        }
    }

    $numeric_fields = array('year', 'abv', 'appearance', 'taste', 'smell', 'mouthfeel', 'overall');

    foreach ($numeric_fields as $numeric_field) {
        $actual_numeric_field = 'dbb_' . $numeric_field;
        $value = $_GET[$actual_numeric_field];

        // error_log("\$numeric_field = $numeric_field, \$value = $value");
                
        if (is_numeric($value) || $value == '') {

        } else {
            array_push($validation_errors, humanize($numeric_field) . " should be a number or left blank");
        }
    }

    # verify that dbb_review_date_start and dbb_review_date_end are
    # well formatted dates and that dbb_review_date_start comes before dbb_review_date_end
  
    # valid dates look like MM-DD-YYYY
    # only '/' is accepted as a separator
    # single digit months and days must be 0 padded
    # this code does not check whether the dates are valid gregorian dates

    # validate the date fields; these combinations are valid:
    # start_date    end_date            comment
    # ----------    --------            -------
    # blank         blank               date filter unused
    # blank         valid date          any dates <= end_date
    # valid date    blank               any dates >= start_date
    # valid date <= valid date          any dates in the range start_date...end_date, start_date must be <= end_date

    # likewise, these combinations should fail
    # start_date    end_date            comment
    # ----------    --------            -------
    # blank         non-date            i.e. text, improper formatting
    # non-date      blank               i.e. text, improper formatting
    # non-date      non-date            any dates >= start_date
    # valid date > valid date           valid dates supplied, but start_date is after end_date

    # Note: the following code doesn't check for valid gregorian dates...so 31-92-2009 could pass

    # each date_str should be a date in a valid format -OR- ''
    $start_date_str = $_GET[$dbb_prefix . 'review_date_start'];
    $end_date_str = $_GET[$dbb_prefix . 'review_date_end'];
    
    $start_date = validateDate($start_date_str);
    $end_date = validateDate($end_date_str);

    $dates_valid = true;
    if (! $start_date && $start_date_str != '') {
        array_push($validation_errors, humanize('review_date_start') . " should be a date in MM-DD-YYYY format");
        $dates_valid = false;
    }
    if (! $end_date && $end_date_str != '') {
        array_push($validation_errors, humanize('review_date_end') . " should be a date in MM-DD-YYYY format");
        $dates_valid = false;
    }

    # start_date should preceed end_date (or be the same)
    if ($start_date && $end_date && ! ($start_date <= $end_date)) {
        array_push($validation_errors, "Review date: start of date range cannot be after the end");
        $dates_valid = false;
    }


    if ($validation_errors) {
        $output = <<<HTML
            <div class="dbb_search_results" id="dbb_validation_errors">
            <p id='dbb_error_header' class='dbb_error_header'><strong style='color:red'>Invalid search terms. Please fix these problems.</strong></p>
            <ul>
        HTML;

        foreach ($validation_errors as $validation_error) {
            $output .= "<li>$validation_error</li>\n";
        }

        $output .= <<<HTML
            </ul>
            </div>
        HTML;
        
        return $output;
    } else {
        return false; # passed validation, no errors returned
    }
}

function validateDate($date, $format = 'm-d-Y') {

    # returns true if the date is a valid date in MM/DD/YYYY format, with a
    
    $d = DateTime::createFromFormat($format, $date);

    if ($d && $d->format($format) == $date) {
        return $d;
    } else {
        return false;        
    }
        
}

function convert_compare_operator($compare, $type = 'text') {

    # convert a human readable comparison operator to one for use in database query
    
    if ($type == 'numeric') {

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

    } elseif ($type == 'text') {
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
    } else {
        die("invalid type $type parameter specified");
    }

    return $compare_operator;

}