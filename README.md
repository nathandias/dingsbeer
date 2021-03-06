# dingsbeerblog / dingsbeerreviews

Beer Review custom post type import, entry, display and search for Ding's Beer Blog website

# Plugin Installation

1. Go to: siteurl.com/wp-admin/ : Plugins -> Add New -> Upload Plugin and upload dbb_plugin.php
2. Activate the plugin

# Divi Parent and Child Theme Installation
1. Go to: siteurl.com/wp-admin/ : Appearance > Themes > Add New > Upload Theme and upload Divi.zip
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

# Classic Post Editor
1. Go to: siteurl.com/wp-admin/ : Appearance > Plugins > Add New
2. Search for the WordPress Classic Editor and install it

The classic post editor is needed to edit custom fields (ABV, Appearance, etc.). Once the classic editor is installed,
the dingsbeer plugin should automatically use it for the beer review custom post type.

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

## Search Page Styling
You can view the page source of the generated search page to see which page elements have css id and/or class selectors.

Hints:
- The search form is enclosed in a div with id='dbb_search_form' and class='dbb_search_form'.
- The search results are enclosed in a div with id='dbb_search_results' and class='dbb_search_results'.
- Form validation errors are enclosed in a div with id='dbb_validation_errors' and class='dbb_search_results'.
- The form labels and inputs have distinct ids and possibly classes. View the page source to see the selectors.

You can use the ids and classes to select various elements of the page for css styling. One easy place to add the custom css styles
is the WordPress Theme Customizer.

Here are some examples:


        # bold all the form labels
        div#dbb_search_form .form_label {
                font-weight:bold;
        }

        # color only the beer_review label blue
        div#dbb_search_form #dbb_beer_search_beer_name_label {
                color: blue;
        }

        # indent the search reults
        div#dbb_search_results ul {
                margin-left:3em;
        }

        # style the search submit button
        div#dbb_search_form input[type=submit]  {
                background-color:green;
                color:white;
                border:none;
                display: block;
                margin: auto;
                margin-bottom: 2em;
                font-size: 1.2em;
                padding: 1em 1.5em;
                cursor: pointer;
        }

# Archive / Taxonomy Page Styling

On any of the full archive or taxonomy archive pages (/beer/, /format/\<format-slug\>, /brewery/\<brewery-slug\>, /style/\<style-slug\>),
WordPress wraps each entry in an <article> tag, which includes 'dingsbeerblog_beer' as class selector. This means that you can target the articles for styling:

        # add padding after each article listing
        article.dingsbeerblog_beer {
                padding-bottom: 3em;
        }

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

# Questions / Comments / Feedback / Bug Reports
Email nathan.dias@gmail.com

## Version 0.5 Changes
- fix: quotation marks dont work in search dropdown selects bug
- fix: innaccurate results when searching numeric fields
- possible fix: nonce does not verify on mobile
- add Codeception acceptance tests to version control
- enable comments on beer custom post type
- set comments_status=open when importing beers from CSV
- improved: enclose custom fields on single beer listing in a <div>
