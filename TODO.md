# Development To-Do List

## Customize DIVI them
- add child theme code
- add single.php
- add archive.php
- add taxonomy.php


## FIX
- brewery name special chars display correctly in admin but not in search form



## Import from CSV feature (import_csv.php)
X investigate strange characters on imports or display
X clean up error reporting message
X handle file upload errors
X use beer mug icon

## Search page (search.php)
- search should work with taxonomy terms
- search by title, notes and review date
- prettify search form according to Adrian's spec

## Single Beer Review output
X rename A, S, T, M, O labels according to Appearance, Smell, Taste, Mouthfeel, Overall


# Investigate strange characters in imports:
## Addressed ( added file encoding dropdown to import form)
Urthel SaisoonAire
ImprovisaciÃ³n Oatmeal Rye India-style Brown Ale
SÃ©rie Signature Kellerbier

Notes:
These characters display correctly in the original Google Sheet, but do not display properly
when exported to CSV and then opened on MS-Excel on Windows.

Also, they do not display properly when imported into Wordpress.

Suggestions:
- try opening the file in an open source spreadsheet program on Windows
    - this worked successfully using Windows Powershell : ImportCsv file | Out-GridView
    - unsuccessful in Office 365 Online
    - successful on reimport to Google Sheets
- this seems to be a character encoding program (i.e. file not recognized as UTF-8)
- can I mark the file as UTF-8 encoding? YES
    - In Google Sheets, export as Microsoft Excel (which will be Windows Western European encoding)
    - Open .xlsx file in MS-Excel, save as CSV (comma separated values) with Tools->Web Options->Encoding = UTF-8
    - import the resulting file

- can I set the encoding on read to UTF-8?






