<?php

function enclose_td ($text) {
    return "<td>" . $text . "</td>";
}

global $wpdb;

// Table name
$tablename = $wpdb->prefix."customplugin";


?>
<h2>All Entries</h2>

<!-- Form -->
<form method='post' action='<?= $_SERVER['REQUEST_URI']; ?>' enctype='multipart/form-data'>
  <input type="file" name="import_file" >
  <input type="submit" name="butimport" value="Import">
</form>

<?php

// Import CSV
if(isset($_POST['butimport'])){

  // File extension
  $extension = pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION);

  // If file extension is 'csv'
  if(!empty($_FILES['import_file']['name']) && $extension == 'csv'){

    $totalInserted = 0;

    // Open file in read mode
    $csvFile = fopen($_FILES['import_file']['tmp_name'], 'r');

    // fgetcsv($csvFile); // Skipping header row

    echo "<table width='100%' border='1' style='border-collapse: collapse'>\n";
    echo "<tbody>\n";


    // Read file
    while(($csvData = fgetcsv($csvFile)) !== FALSE){

      $csvData = array_map("utf8_encode", $csvData);

      fgetcsv($csvFile); // Skipping header row

      // Row column length
      $dataLen = count($csvData);

      // Skip row if length != 15
      if( !($dataLen == 15) ) continue;

      // Assign value to variables
      list ($brewery, $beer_name, $series_name, $year, $style, $abv, $format, $total,
        $a, $s, $t, $m, $o, $review_date, $notes) = array_map('trim', $csvData);

        $post_id = wp_insert_post(array(
            'post_title'=> $beer_name, 
            'post_type'=>'dingsbeerblog_beer', 
            'post_content'=> $notes,
            'post_status' => 'published',
            'comment_status' => 'closed',
            'pingback_status' => 'closed',
        ));

     if ($post_id) {
        // insert post meta
        $meta_key_names = array('brewery', 'series_name', 'year', 'style', 'abv', 'format', 'total',
            'a', 's', 't', 'm', 'o', 'review_date');

        foreach ($meta_key_names as $meta_key_name) {
            add_post_meta($post_id, $meta_key_name, ${"$meta_key_name"});
        }

     }
      
      $totalInserted++;
    }
    
    echo "<h3 style='color: green;'>Total record Inserted : ".$totalInserted."</h3>";


  }else{
    echo "<h3 style='color: red;'>Invalid Extension</h3>";
  }

}