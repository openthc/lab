#!/bin/bash -x
#
# OpenTHC Test Runner
#

set -o errexit
set -o nounset

x=${OPENTHC_TEST_BASE:-}
if [ -z "$x" ]
then
	echo "You have to define the environment first"
	exit 1
fi

f=$(readlink -f "$0")
d=$(dirname "$f")

cd "$d"

output_base="webroot/test-output"
output_main="$output_base/index.html"
mkdir -p "$output_base"

code_list=(
	boot.php api/ bin/ controller/ lib/ sbin/ test/ view/
)


OUTPUT_BASE="${output_base}"
OUTPUT_MAIN="${output_main}"
SOURCE_LIST="${code_list}"

export OUTPUT_BASE OUTPUT_MAIN SOURCE_LIST


#
# Lint
if [ ! -f "$output_base/phplint.txt" ]
then

	echo '<h1>Linting...</h1>' > "$output_main"

	find "${code_list[@]}" -type f -name '*.php' -exec php -l {} \; \
		| grep -v 'No syntax' || true \
		>"$output_base/phplint.txt"

	[ -s "$output_base/phplint.txt" ] || echo "Linting OK" >"$output_base/phplint.txt"

fi


#
# PHP-CPD
vendor/openthc/common/test/phpcpd.sh


#
# PHPStan
vendor/openthc/common/test/phpstan.sh


#
# PHPUnit
vendor/openthc/common/test/phpstan.sh "$@"


#
# Final Output
test_date=$(date)
test_note=$(tail -n1 "$out_file")

cat <<HTML > "$output_main"
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="initial-scale=1, user-scalable=yes">
<meta name="theme-color" content="#069420">
<style>
html {
	font-family: sans-serif;
	font-size: 1.5rem;
}
</style>
<title>Test Result ${test_date}</title>
</head>
<body>
<h1>Test Result ${test_date}</h1>
<h2>${note}</h2>
<p>Linting: <a href="phplint.txt">phplint.txt</a></p>
<p>PHPCPD: <a href="phpcpd.txt">phpcpd.txt</a></p>
<p>PHPStan: <a href="phpstan.xml">phpstan.xml</a> and <a href="phpstan.html">phpstan.html</a></p>
<p>PHPUnit: <a href="phpunit.txt">phpunit.txt</a>, <a href="phpunit.xml">phpunit.xml</a> and <a href="phpunit.html">phpunit.html</a></p>
<p>Textdox: <a href="testdox.txt">testdox.txt</a>, <a href="testdox.xml">testdox.xml</a> and <a href="testdox.html">testdox.html</a></p>
</body>
</html>
HTML
