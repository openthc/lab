#!/bin/bash
#
#

set -o errexit

composer update \
	--classmap-authoritative \
	--no-dev

npm update

# Copy stuff to our webroot
cp node_modules/jquery/dist/jquery.min.js webroot/js/jquery.min.js
cp node_modules/jqueryui/jquery-ui.min.js webroot/js/jquery-ui.min.js
cp node_modules/jqueryui/jquery-ui.min.css webroot/css/jquery-ui.min.css



# Google Fonts
wget https://openthc.com/pub/fonts/CedarvilleCursive-Regular.ttf
wget https://openthc.com/pub/fonts/HomemadeApple-Regular.ttf

vendor/tecnickcom/tcpdf/tools/tcpdf_addfont.php --fonts=CedarvilleCursive-Regular.ttf
vendor/tecnickcom/tcpdf/tools/tcpdf_addfont.php --fonts=HomemadeApple-Regular.ttf

rm CedarvilleCursive-Regular.ttf
rm HomemadeApple-Regular.ttf
