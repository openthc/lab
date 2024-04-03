#!/bin/bash
#
#

set -o errexit
set -o errtrace
set -o nounset
set -o pipefail

BIN_SELF=$(readlink -f "$0")
APP_ROOT=$(dirname "$BIN_SELF")

composer install --no-ansi --no-dev --no-progress --quiet --classmap-authoritative

npm install --quiet

. vendor/openthc/common/lib/lib.sh

copy_bootstrap
copy_fontawesome
copy_jquery

# Google Fonts
curl -O https://openthc.com/pub/font/CedarvilleCursive-Regular.ttf
curl -O https://openthc.com/pub/font/HomemadeApple-Regular.ttf

vendor/tecnickcom/tcpdf/tools/tcpdf_addfont.php --fonts=CedarvilleCursive-Regular.ttf
vendor/tecnickcom/tcpdf/tools/tcpdf_addfont.php --fonts=HomemadeApple-Regular.ttf

rm CedarvilleCursive-Regular.ttf
rm HomemadeApple-Regular.ttf
