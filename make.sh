#!/bin/bash
#
#

set -o errexit
set -o errtrace
set -o nounset
set -o pipefail

composer update --no-ansi --no-dev --no-progress --quiet --classmap-authoritative

npm install --quiet

# Copy stuff to our webroot
mkdir -p webroot/css webroot/js webroot/vendor
cp node_modules/jquery/dist/jquery.min.js webroot/js/jquery.min.js
cp node_modules/jquery-ui/dist/jquery-ui.min.js webroot/js/jquery-ui.min.js
cp node_modules/jquery-ui/dist/themes/base/jquery-ui.min.css webroot/css/jquery-ui.min.css


# Google Fonts
wget https://openthc.com/pub/font/CedarvilleCursive-Regular.ttf
wget https://openthc.com/pub/font/HomemadeApple-Regular.ttf

vendor/tecnickcom/tcpdf/tools/tcpdf_addfont.php --fonts=CedarvilleCursive-Regular.ttf
vendor/tecnickcom/tcpdf/tools/tcpdf_addfont.php --fonts=HomemadeApple-Regular.ttf

rm CedarvilleCursive-Regular.ttf
rm HomemadeApple-Regular.ttf
