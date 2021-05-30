<?php

function enclose_td ($text) {
    return "<td>" . $text . "</td>";
}


$totalInserted = 0;

// Open file in read mode
$csvFile = fopen("dingsbeerblog_reviews.csv", 'r');

fgetcsv($csvFile); // Skipping header row

// Read file
while(($csvData = fgetcsv($csvFile)) !== FALSE){

    echo "***\n";
    $csvData = array_map("utf8_encode", $csvData);

    // Row column length
    $dataLen = count($csvData);

    // Skip row if length != 15
    if( !($dataLen == 15) ) continue;

    // Assign value to variables
    $values = list ($brewery, $beer_name, $series_name, $year, $style, $abv, $format, $total,
        $a, $s, $t, $m, $o, $review_date, $notes) = array_map('trim', $csvData);


    $meta_key_names = array('brewery', 'series_name', 'year', 'style', 'abv', 'format', 'total',
        'a', 's', 't', 'm', 'o', 'review_date');


    foreach ($meta_key_names as $meta_key_name) {
        echo "$meta_key_name: ${$meta_key_name}\n";
    }

    echo "***\n";



}