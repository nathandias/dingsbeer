#!/bin/bash

# remove old files
rm dbb_plugin.zip yoko-child.zip Divi-child.zip csv_data.zip

# zip up the plugin
cp ./README.md ./TODO.md dingsbeer
zip -x "dingsbeer/test" -x "dingsbeer/test/*" -x "dingsbeer/tests" -x "dingsbeer/tests/*" -x "dingsbeer/vendor" -x "dingsbeer/vendor/*" -x "dingsbeer/.gitignore" -r dbb_plugin.zip dingsbeer
rm dingsbeer/README.md dingsbeer/TODO.md

# zip up the yoko-child theme
zip -r yoko-child.zip yoko-child

# zip up the divi-child theme
zip -r Divi-child.zip Divi-child

# zip up the test data
cd dingsbeer/test/
zip csv_data.zip *
cd ../..
mv dingsbeer/test/csv_data.zip .

