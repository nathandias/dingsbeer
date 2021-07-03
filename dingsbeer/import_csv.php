<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


?>
<h2>Import Beer Reviews from CSV file</h2>
<h3>Usage notes:</h3>
<ol>
<li>File size must be <= 2MB due to PHP file upload limits. If file size > 2MB, split into multiple files.</li>
<li>File is expected to be in comma separated values format (CSV) with either UTF-8 or Windows 1252 encoding. Specify the encoding using the dropdown.</li>
<li><strong>Recommended (works on Windows 10)</strong>:

  <ul style="list-style-type:circle; margin-left: 10px; margin-top: 5px">
    <li>Download the Google Sheet in Microsoft Excel (.xlsx) format.</li>
    <li>Open in Excel, Save As... CSV (comma delimited)</li>
    <li>From the Save As Dialog: Click Tools->Web Options->Encoding and select "UTF-8"</li>
    <li>Make sure "UTF-8" is selected in the dropdown, before importing the file.</li>
  </ul>
</li>
<li>First row should contain column headers, and will be skipped when parsing</li>
<li>Expected column ordering/naming is:<br/>
Brewery, Beer Name, Series Name, Year, Style, ABV, Format, Total, Appearance, Smell, Taste, Mouthfeel, Overall, Review Date, Notes</li>
</ol>

<!-- Form -->
<form method='POST' action='<?= $_SERVER['REQUEST_URI']; ?>' enctype='multipart/form-data'>

<?php wp_nonce_field( 'dbb_import_csv', '_dbb_nonce' ); ?>
  
<table style="text-align:left">
<tbody>
  <tr><th><label for="file_encoding">File Encoding</label></th><td>
    <select name="file_encoding" id="file_encoding">
      <option value="UTF-8" selected>UTF-8 (default)</option>
      <option value="CP1252">Western European (Windows 1252)</option>
    </select>
  </td></tr>
  <tr><th><label for="import_file">CSV file</label></th><td><input type="file" name="import_file" /></td></tr>
  </tbody>
  </table>

  <br/>

  <input type="hidden" name="MAX_FILE_SIZE" value="2097152" />
  <input type="submit" name="butimport" value="Import">
</form>

<?php

// Import CSV




if(isset($_POST['butimport'])) {


  if ( ! isset( $_POST['_dbb_nonce'] ) 
    || ! wp_verify_nonce( $_POST['_dbb_nonce'], 'dbb_import_csv' ) 
  ) {
   print 'Sorry, your nonce did not verify.';
   exit;
  } else {
   // process form data

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

          
          if ($_POST['file_encoding'] == 'UTF-8') {
            $csvData = array_map("utf8_normalize", $csvData);
          } elseif ($_POST['file_encoding'] == 'CP1252') {
            $csvData = array_map('encode_win1252_to_utf8', $csvData);
          } else {
            throw new RuntimeException ("Invalid file encoding specified: " . $_POST['file_encoding']);
          }


          // Row column length
          $dataLen = count($csvData);

          // Skip row if length != 15
          if( !($dataLen == 15) ) {
            error_log("skipping row # $rowCount; column count != 15");
            throw new RuntimeException("Invalid file format: rows should have exactly 15 columns");
            continue;
          }

          // Assign value to variables (and remove whitespace)
          $field_names = ['brewery', 'beer_name', 'series_name', 'year', 'style', 'abv', 'format', 'total',
            'appearance', 'smell', 'taste', 'mouthfeel', 'overall', 'review_date', 'notes'];

          // read the CSV data into an associative array, field_name => value
          $i = 0;
          $data = [];
          foreach ($field_names as $field_name) {
            $data[$field_name] = trim($csvData[$i]);
            error_log("$field_name = " . $csvData[$i]);
            $i++;
          }

          // remove a trailing percent sign(s) from ABV if it's included
          $data['abv'] = rtrim($data['abv'], "\%");

          // normalize the review date format
          $review_date = $data['review_date'];
          $review_date = new DateTime($review_date);
          $data['review_date'] = $review_date->format('Y-m-d h:i:s');

          // encode htmlentities for each key, value
          foreach ($data as $value) {
            $value = htmlentities($value);
          }

          // alias the data so that we can reference $data['brewery'] as $brewery
          // and changing $brewery will update $data['brewery'];
          foreach ($field_names as $field_name) {
            ${$field_name} =& $data[$field_name];
          }

          # uses WordPress built-ins to insert data, so let WordPress handle escaping the queries

          $post_id = wp_insert_post(array(
            'post_title'=> $beer_name, 
            'post_type'=>'dingsbeerblog_beer', 
            'post_content'=> $notes,
            'post_status' => 'publish',
            'post_date' => $review_date,
            'comment_status' => 'open',
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
                  add_post_meta($post_id, $meta_key_name, ${"$meta_key_name"});
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

}


# functions to convert/normalize character encodings
function encode_win1252_to_utf8($data) {
  return mb_convert_encoding($data, "UTF-8", "CP1252");
}

function utf8_normalize($data) {
  
  # i.e. just in case the uploaded file isn't really UTF-8 encoded
  return mb_convert_encoding($data, "UTF-8", "UTF-8");

}
