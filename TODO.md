# Development To-Do List

# Search Page
- validate the review date fields...entries are valid dates + 1st date <= 2nd date
## Search page (search.php)
- add form reset button
## Optional Addition
- shortcodes that displays a post element, given the post_title slug as a query string to the page

---------------------------------------
# DONE
## Customize DIVI them
- add child theme code
- add single.php

## Search page (search.php)
- nonce/csfr token added to beer review search form
- search by review date
- add form validation (numeric, text)

## Security audit
- escape SQL values for beer review search form
- escape SQL values for csv file import form

## FIXED
- still a few special characters with strange display:
see: "pivovar" -- works with Windows 1252 encoding

---------------------------------------

# Other Notes
## Notes about Nonces and SQL escaping
used with user actions
1. custom post editing fields should be already protected by word press
2. other places where I have forms
Search Page: only does non-destructive queries (i.e. select)...so don't need it?
Import Page: definitely changes (adds to) the database, so use a nonce
*I used nonces on both forms*






