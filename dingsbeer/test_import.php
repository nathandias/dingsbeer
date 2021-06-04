<?php

header('Content-Type: text/html; charset=UTF-8');

function enclose_td ($text) {
    return "<td>" . $text . "</td>";
}

$filename = "../test/dingsbeerblog_reviews.csv";

// $fp = fopen($filename,'r') or die("can't open file");
// print "<table>\n";
// while($csv_line = fgetcsv($fp)) {
//     print '<tr>';
//     for ($i = 0, $j = count($csv_line); $i < $j; $i++) {
//         print '<td>'.htmlentities($csv_line[$i]).'</td>';
//     }
//     print "</tr>\n";
//     $lines_read++;
// }
// print "</table>\n";
// print "<h1>lines read: $lines_read";
// fclose($fp) or die("can't close file");


$totalInserted = 0;

// Open file in read mode
$csvFile = fopen($filename, 'r');

fgetcsv($csvFile); // Skipping header row

$row_count = 0;

// Read file
while(($csvData = fgetcsv($csvFile)) !== FALSE){

    echo "*** Reading row # " . ++$row_count . "\n";

    // Row column length
    $dataLen = count($csvData);

    // Skip row if length != 15
    if( !($dataLen == 15) ) continue;

    // Assign value to variables
    $values = list ($brewery, $beer_name, $series_name, $year, $style, $abv, $format, $total,
        $a, $s, $t, $m, $o, $review_date, $notes) = array_map('trim', $csvData);


    $meta_key_names = array('brewery', 'beer_name', 'series_name', 'year', 'style', 'abv', 'format', 'total',
        'a', 's', 't', 'm', 'o', 'review_date', 'notes');


    foreach ($meta_key_names as $meta_key_name) {
        echo "$meta_key_name:" . htmlentities(${$meta_key_name}) . "\n";

    }

}
echo "===\n";
echo "Total rows read: $row_count\n";