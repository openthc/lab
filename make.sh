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

# /**
#  * not sure how to implement this yet
#  */
# function install_fonts()
# {
	Google Fonts
	# // curl -O https://openthc.com/pub/font/CedarvilleCursive-Regular.ttf
	# // curl -O https://openthc.com/pub/font/HomemadeApple-Regular.ttf
#
	# // vendor/tecnickcom/tcpdf/tools/tcpdf_addfont.php --fonts=CedarvilleCursive-Regular.ttf
	# // vendor/tecnickcom/tcpdf/tools/tcpdf_addfont.php --fonts=HomemadeApple-Regular.ttf
#
	# // rm CedarvilleCursive-Regular.ttf
	# // rm HomemadeApple-Regular.ttf
#
# }


# libsodium is a special case
#if [ -d "libsodium.js" ]
#then
#	cd libsodium.js
#	git checkout master
#	git pull
#	cd -
#	git checkout 0.7.13
#else
#	mkdir libsodium.js
#	cd libsodium.js
#	git clone https://github.com/jedisct1/libsodium.js.git ./
#	git checkout 0.7.13
#	cd -
#fi
#mkdir -p webroot/vendor/libsodium/
#cp libsodium.js/dist/browsers/sodium.js webroot/vendor/libsodium/


#
# Document CrashCourse?
# mkdir -p ./webroot/crash-course

#asciidoctor \
#	--verbose \
#	--backend=html5 \
#	--require=asciidoctor-diagram \
#	--section-numbers \
#	--out-file=./webroot/crash-course.html \
#	./content/crash-course.ad

#asciidoctor \
#	--verbose \
#	--backend=revealjs \
#	--require=asciidoctor-diagram \
#	--require=asciidoctor-revealjs \
#	--out-file=./webroot/crash-course-slides.html \
#	./content/crash-course.ad
