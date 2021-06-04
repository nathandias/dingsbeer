<?php

// Add menu
function dingsbeerblog_admin_menu() {

    add_menu_page("Import Beer Reviews", "Import Beer Reviews","manage_options", "dingsbeerblog_admin", "importBeersFromCSV", plugins_url('/dingsbeer/img/icon.png'));
 
 }

 add_action("admin_menu", "dingsbeerblog_admin_menu");

function importBeersFromCSV(){
    include "import_csv.php";
}