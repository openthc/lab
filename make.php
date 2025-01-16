#!/usr/bin/php
<?php
/**
 * Make Helper
 *
 *
 */

// can 'use' here, cause there is maybe no autoloader yet

if ( ! is_file(__DIR__ . '/vendor/autoload.php')) {
	$cmd = [];
	$cmd[] = 'composer';
	$cmd[] = 'install';
	$cmd[] = '--classmap-authoritative';
	$cmd[] = '2>&1';
	echo "Composer:\n";
	passthru(implode(' ', $cmd), $ret);
	var_dump($ret);
}

require_once(__DIR__ . '/boot.php');

$doc = <<<DOC
OpenTHC Directory Make Helper

Usage:
	make [options]

Commands:
	install
	search-update  (calls bin/cli.php search-update)

Options:
	--filter=<FILTER>   Some Filter for PHPUnit

DOC;
// $cli_args

\OpenTHC\Make::composer();

\OpenTHC\Make::npm();

\OpenTHC\Make::install_bootstrap();

\OpenTHC\Make::install_fontawesome();

\OpenTHC\Make::install_jquery();

create_homepage();

/**
 *
 */
function create_homepage() {

	$cfg = \OpenTHC\Config::get('openthc/www/origin');
	$url = sprintf('%s/home', $cfg);
	$req = _curl_init($url);
	$res = curl_exec($req);
	$inf = curl_getinfo($req);
	if (200 == $inf['http_code']) {
		$file = sprintf('%s/webroot/index.html', APP_ROOT);
		$data = $res;
		file_put_contents($file, $data);
	}

}

/**
 * not sure how to implement this yet
 */
function install_fonts()
{
	# Google Fonts
	// curl -O https://openthc.com/pub/font/CedarvilleCursive-Regular.ttf
	// curl -O https://openthc.com/pub/font/HomemadeApple-Regular.ttf

	// vendor/tecnickcom/tcpdf/tools/tcpdf_addfont.php --fonts=CedarvilleCursive-Regular.ttf
	// vendor/tecnickcom/tcpdf/tools/tcpdf_addfont.php --fonts=HomemadeApple-Regular.ttf

	// rm CedarvilleCursive-Regular.ttf
	// rm HomemadeApple-Regular.ttf

}


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
