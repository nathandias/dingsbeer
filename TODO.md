# Development To-Do List
## Security audit
- nonce/csfr token added to beer review search form
- escape SQL values for beer review search form
- escape SQL values for csv file import form
## FIX
- still a few special characters with strange display:
see: "pivovar"
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
- search by review date
- add form validation (numeric, text)

---------------------------------------

# Other Notes
## Notes about Nonces and SQL escaping
used with user actions
1. custom post editing fields should be already protected by word press
2. other places where I have forms
Search Page: which does GET / select queries only
Import Page: does add data to the database

Should probably use a nonce on the Import Page at a minimum
Not necessarily needed.






