#!/bin/bash
PACKAGE_VERSION=`grep "Version" dingsbeer/plugin.php | sed 's/^ \* Version: //'`

echo "Creating package for version = ${PACKAGE_VERSION}"

echo "*** packaging main plugin files ***"
# remove old files
rm dbb_plugin*.zip yoko-child*.zip Divi-child*.zip csv_data*.zip

# zip up the plugin
cp ./README.md ./TODO.md dingsbeer
zip -x "dingsbeer/test" -x "dingsbeer/test/*" -x "dingsbeer/tests" -x "dingsbeer/tests/*" -x "dingsbeer/vendor" -x "dingsbeer/vendor/*" -x "dingsbeer/.gitignore" -r "dbb_plugin.${PACKAGE_VERSION}.zip" dingsbeer
rm dingsbeer/README.md dingsbeer/TODO.md

echo "*** packaging yoko child theme files ***"
# zip up the yoko-child theme
zip -r "yoko-child.${PACKAGE_VERSION}.zip" yoko-child

echo "*** packaging Divi child theme files ***"
# zip up the divi-child theme
zip -r "Divi-child.${PACKAGE_VERSION}.zip" Divi-child

echo "*** packaging CSV testcases ***"
# zip up the test data
cd dingsbeer/test/
zip "csv_data.${PACKAGE_VERSION}.zip" *
cd ../..
mv dingsbeer/test/csv_data.${PACKAGE_VERSION}.zip .

