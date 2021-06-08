# dingsbeerblog / dingsbeerreviews

Beer Review custom post type import, entry, display and search for Ding's Beer Blog website

# Plugin Installation

1. Go to: siteurl.com/wp-admin/ : Plugins -> Add New -> Upload Plugin and upload dbb_plugin.php
2. Activate the plugin

# Yoko Child Theme Installation

1. Go to: siteurl.com/wp-admin/ : Appearance > Themes > Add New > Upload Theme and upload yoko-child.zip

The child theme provides customized page templates for displaying:

| URL             | Description                                                                       |
|-----------------|-----------------------------------------------------------------------------------|
| /beer/          | alphabetical archive of all beer reviews (archive=dingsbeerblog_beer.php)         |
| /brewery/slug   | archive of reviews for specified brewery (taxonomy.php)                           |
| /style/slug     | archive of reviews for specified style (taxonomy.php)                             |
| /format/slug    | archive of reviews for specified format (taxonomy.php)                            |
| /slug:beer_name | a (single) beer review (single-dingsbeerblog_beer.php)                            |

# Beer Review Import
1. Go to: siteurl.com/wp-admin/ : Import Beer Reviews and follow the instructions there to import
reviews from the Google Sheets data.
*Note: upload file sizes must be <= 2MB, format must by CSV, and encoding must be UTF-8 or Windows 1252*

# Adding a New Beer
1. Go to: siteurl.com/wp-admin/ : Beers > New Beer
2. Enter a title (beer name) and content (notes) in the main editing area
3. In the "Custom Fields" section, select the Name of the field you want to add from the dropdown, and enter a corresponding value.
4. click "Publish"

# Search Page Setup

1. Go to: dingsbeerblog.com/wp-admin/ : Pages > Add New
2. Enter a page title and any text you'd like
3. Somewhere on the page, include the shortcode:

        [beer_review_search]

4. Save the page and visit permalink. You should see a form for searching and filtering beer reviews.

# Recommended for Testing
1. Install the optional plugin "Bulk Delete" to remove beers from the database (https://tinyurl.com/bulk-delete-plugin)
2. You should be able to use various WordPress plugins and widgets to display custom post data, taxonomy and fields.
E.g. List Custom Taxonomy Widget (https://wordpress.org/plugins/list-custom-taxonomy-widget/)