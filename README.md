# dingsbeerblog / dingsbeerreviews

Beer Review custom post type import, entry, display and search for Ding's Beer Blog website

# Plugin Installation

1. Go to: siteurl.com/wp-admin/ : Plugins -> Add New -> Upload Plugin and upload dbb_plugin.php
2. Activate the plugin

# Divi Parent and Child Theme Installation
1. Go to: siteurl.com/wp-admin/ : Appearance > Themese > Add New > Upload Theme and upload Divi.zip
2. Go to: siteurl.com/wp-admin/ : Appearance > Themes > Add New > Upload Theme and upload Divi-child.zip

(alternative: upload yoko-child.zip to use the yoko child theme using the same process)

The child theme provides customized page templates for displaying:

| URL             | Description                                                                       |
|-----------------|-----------------------------------------------------------------------------------|
| /beer/          | alphabetical archive of all beer reviews                                          |
| /brewery/slug   | archive of reviews for specified brewery                                          |
| /style/slug     | archive of reviews for specified style                                            |
| /format/slug    | archive of reviews for specified format                                           |
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


# Child Theme Notes
- the plugin provides the function dbb_display_beer_review_custom_fields($post_id) to output custom fields for the post
- use it in the loop like:

   dbb_display_beer_review_custom_fields($post->ID)

See Divi-child.zip > single-dingsbeerblog_beer.php for an example of usage.
See dbb_plugin.zip > theme_support.php for the code that outputs the custom fields.

# Optional support plugins
1. Use "Bulk Delete" to remove beer review posts from the database en masse (https://wordpress.org/plugins/bulk-delete/)
2. Use "Taxo Press" that can be used to bulk delete unused taxonomy terms (https://wordpress.org/plugins/simple-tags/)
3. Try List Custom Taxonomy Widget (and similar) to display custom post data, taxonomy and fields (https://wordpress.org/plugins/list-custom-taxonomy-widget/)

