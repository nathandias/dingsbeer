<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


?>
<h2>Import Beer Reviews from CSV file</h2>
<h3>Usage notes:</h3>
<ol>
<li>File size must be <= 2MB due to PHP file upload limits. If file size > 2MB, split into multiple files.</li>
<li>File is expected to be in comma separated values format (CSV) with UTF-8 encoding</li>
<li>First row should contain column headers, and will be skipped when parsing</li>
<li>Expected column ordering/naming is:<br/>
Brewery, Beer Name, Series Name, Year, Style, ABV, Format, Total, Appearance, Smell, Taste, Mouthfeel, Overall, Review Date, Notes</li>
</ol>

<!-- Form -->
<form method='post' action='<?= $_SERVER['REQUEST_URI']; ?>' enctype='multipart/form-data'>
  <input type="file" name="import_file" />
  <input type="hidden" name="MAX_FILE_SIZE" value="2097152" />
  <input type="submit" name="butimport" value="Import">
</form>

<?php

// Import CSV

if(isset($_POST['butimport'])) {

  try {
   
    // Undefined | Multiple Files | $_FILES Corruption Attack
    // If this request falls under any of them, treat it invalid.
    if (
        !isset($_FILES['import_file']['error']) ||
        is_array($_FILES['import_file']['error'])
    ) {
        throw new RuntimeException('Invalid parameters.');
    }

    // Check $_FILES['import_file']['error'] value.
    switch ($_FILES['import_file']['error']) {
      case UPLOAD_ERR_OK:
          break;
      case UPLOAD_ERR_NO_FILE:
          throw new RuntimeException('No file sent.');
      case UPLOAD_ERR_INI_SIZE:
      case UPLOAD_ERR_FORM_SIZE:
          throw new RuntimeException('Exceeded filesize limit.');
      default:
          throw new RuntimeException('Unknown errors.');
    }

    // You should also check filesize here.
    if ($_FILES['import_file']['size'] > 2097152) {
      throw new RuntimeException('Exceeded filesize limit.');
    }

    // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
    // Check MIME Type by yourself.
    
    // File MIME type checking is currently inactive
    // Okay for now, since only admin users can access this page
    // would like to fix this eventually.


    // $finfo = new finfo(FILEINFO_MIME_TYPE);
    // if (false === $ext = array_search(
    //     $finfo->file($_FILES['import_file']['tmp_name']),
    //     array(
    //         'csv' => 'text/csv',
    //     ),
    //     true
    // )) {
    //     throw new RuntimeException('Invalid file format.');
    // }

    echo '<h3 style="color:green">File uploaded successfully</h3>';

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
      error_log("DOCUMENT ATTACH: " . print_r($_FILES, true));
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
          error_log("skipping row # $rowCount; column count != 15");
          throw new RuntimeException("Invalid file format: rows should have exactly 15 columns");
          continue;
        }

        // Assign value to variables
        list ($brewery, $beer_name, $series_name, $year, $style, $abv, $format, $total,
          $appearance, $smell, $taste, $mouthfeel, $overall, $review_date, $notes) = array_map('trim', $csvData);

        // remove a percent sign from ABV if it's included
        $abv = rtrim($abv, "\%");

        // normalize the review date format
        $review_date = new DateTime($review_date);
        $review_date = $review_date->format('Y-m-d h:i:s');
      
        $post_id = wp_insert_post(array(
          'post_title'=> htmlentities($beer_name), 
          'post_type'=>'dingsbeerblog_beer', 
          'post_content'=> htmlentities($notes),
          'post_status' => 'publish',
          'post_date' => $review_date,
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
            $meta_key_names = array('series_name', 'year', 'abv', 'total',
                'appearance', 'smell', 'taste', 'mouthfeel', 'overall');

            foreach ($meta_key_names as $meta_key_name) {
                add_post_meta($post_id, $meta_key_name, htmlentities(${"$meta_key_name"}));
            }

            // associate taxonomy terms with the post
            // wp_create_term creates a new term if it doesn't exist already

            wp_set_object_terms($post_id, $format, 'format');
            wp_set_object_terms($post_id, $brewery, 'brewery');
            wp_set_object_terms($post_id, $style, 'style');

        }
        
        $totalInserted++;
      }
    
      
      echo "<h3 style='color: green;'>Total record Inserted : ".$totalInserted."</h3>";
    } else {

      throw new RuntimeException('Invalid File Extension');

    }
  } catch (RuntimeException $e) {

      echo "<h3 style='color:red'>" . $e->getMessage() . "</h3>";
  
  }
}
