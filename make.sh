#!/bin/bash
#
# Install Helper
#
# SPDX-License-Identifier: MIT
#

set -o errexit
set -o errtrace
set -o nounset
set -o pipefail

APP_ROOT=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )

cd "$APP_ROOT"

composer install --no-ansi --no-progress --classmap-authoritative

npm install --no-audit --no-fund

php <<PHP
<?php
define('APP_ROOT', __DIR__);
require_once(APP_ROOT . '/vendor/autoload.php');
\OpenTHC\Make::install_bootstrap();
\OpenTHC\Make::install_fontawesome();
\OpenTHC\Make::install_jquery();
\OpenTHC\Make::create_homepage('lab');
PHP

echo "DONE"

# not sure how to implement this yet
# Google Fonts
# curl -O https://openthc.com/pub/font/CedarvilleCursive-Regular.ttf
# curl -O https://openthc.com/pub/font/HomemadeApple-Regular.ttf

# vendor/tecnickcom/tcpdf/tools/tcpdf_addfont.php --fonts=CedarvilleCursive-Regular.ttf
# vendor/tecnickcom/tcpdf/tools/tcpdf_addfont.php --fonts=HomemadeApple-Regular.ttf
#
# rm CedarvilleCursive-Regular.ttf
# rm HomemadeApple-Regular.ttf
#
