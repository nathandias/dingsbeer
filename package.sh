#!/bin/bash
zip -x "dingsbeer/test" -x "dingsbeer/test/*" -x "dingsbeer/.gitignore" -r dbb_plugin.zip dingsbeer
zip -r yoko-child.zip yoko-child
cd dingsbeer/test/
zip csv_data.zip *
cd ../..
mv dingsbeer/test/csv_data.zip .

