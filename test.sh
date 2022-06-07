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
out_file="$output_base/phpunit.txt"
xsl_file="test/phpunit.xsl"

echo '<h1>Running Tests...</h1>' > "$output_main"
vendor/bin/phpunit \
	--configuration="test/phpunit.xml" \
	--log-junit "$output_base/phpunit.xml" \
	--testdox-html "$output_base/testdox.html" \
	--testdox-text "$output_base/testdox.txt" \
	--testdox-xml "$output_base/testdox.xml" \
	test/ \
	2>&1 \
	| tee "$out_file"

[ -f "$xsl_file" ] || curl -qs 'https://openthc.com/pub/phpunit/report.xsl' > "$xsl_file"

xsltproc \
	--nomkdir \
	--output "$output_base/phpunit.html" \
	"$xsl_file" \
	"$output_base/phpunit.xml"


#
# Final Output
dt=$(date)
note=$(tail -n1 "$out_file")

cat <<HTML > "$output_main"
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="initial-scale=1, user-scalable=yes">
<meta name="theme-color" content="#247420">
<link rel="stylesheet" href="https://cdn.openthc.com/bootstrap/4.4.1/bootstrap.css" integrity="sha256-L/W5Wfqfa0sdBNIKN9cG6QA5F2qx4qICmU2VgLruv9Y=" crossorigin="anonymous">
<title>Test Result ${dt}</title>
</head>
<body>
<div class="container mt-4">
<div class="jumbotron">

<h1>Test Result ${dt}</h1>
<h2>${note}</h2>

<p>You can view the <a href="output.txt">raw script output</a>,
or the <a href="output.xml">Unit Test XML</a>
which we've processed <small>(via XSL)</small> to <a href="output.html">a pretty report</a>
which is also in <a href="testdox.html">testdox format</a>.
</p>

</div>
</div>
</body>
</html>
HTML
