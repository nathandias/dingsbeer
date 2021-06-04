<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


?>
<h2>All Entries</h2>

<!-- Form -->
<form method='post' action='<?= $_SERVER['REQUEST_URI']; ?>' enctype='multipart/form-data'>
  <input type="file" name="import_file" >
  <input type="submit" name="butimport" value="Import">
</form>

<?php

// Import CSV


// $uploaded_filename = $tmp_filename = __DIR__ . '/test/dingsbeerblog_reviews.csv';

if(isset($_POST['butimport'])){

  $uploaded_filename = $_FILES['import_file']['name'];

  error_log("DIR: " . __DIR__ );
  error_log("UPLOAD NAME: " . $uploaded_filename);
 
  // File extension
  $extension = pathinfo($uploaded_filename, PATHINFO_EXTENSION);

  // If file extension is 'csv'
  if(!empty($uploaded_filename) && $extension == 'csv'){

    $totalInserted = 0;

    $tmp_filename = $_FILES['import_file']['tmp_name'];
    error_log("TMP NAME: " . $tmp_filename);
    error_log("DOCUMENT ATTACH: " . var_export($_FILES));
    // Open file in read mode

    $csvFile = fopen($tmp_filename, 'r') or die ("can't open file");

    fgetcsv($csvFile); // Skipping header row

    echo "<table width='100%' border='1' style='border-collapse: collapse'>\n";
    echo "<tbody>\n";


    $rowCount = 0;

    // Read file
    while(($csvData = fgetcsv($csvFile)) !== FALSE){

      $rowCount++;
      error_log("attempting insert of row # " . $rowCount);

      $csvData = array_map("utf8_encode", $csvData);

      // Row column length
      $dataLen = count($csvData);

      // Skip row if length != 15
      if( !($dataLen == 15) ) {
        echo "skipping line";
        continue;
      }

      // Assign value to variables
      list ($brewery, $beer_name, $series_name, $year, $style, $abv, $format, $total,
        $a, $s, $t, $m, $o, $review_date, $notes) = array_map('trim', $csvData);

      $post_id = wp_insert_post(array(
        'post_title'=> htmlentities($beer_name), 
        'post_type'=>'dingsbeerblog_beer', 
        'post_content'=> htmlentities($notes),
        'post_status' => 'publish',
        'comment_status' => 'closed',
        'pingback_status' => 'closed',
      ));

      if ($post_id) {
        error_log("inserted post_id = $post_id");
      } else {
        error_log("failed to insert that row");
      }

      
     if ($post_id) {
        // insert post meta
        $meta_key_names = array('brewery', 'series_name', 'year', 'style', 'abv', 'format', 'total',
            'a', 's', 't', 'm', 'o', 'review_date');

        foreach ($meta_key_names as $meta_key_name) {
            add_post_meta($post_id, $meta_key_name, htmlentities(${"$meta_key_name"}));
        }

     }
      
      $totalInserted++;
    }
    
    echo "<h3 style='color: green;'>Total record Inserted : ".$totalInserted."</h3>";


  }else{
    echo "<h3 style='color: red;'>Invalid Extension</h3>";
  }

}