<?php
/**
 * View Lab Results as Text
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

unset($data['Site']);
unset($data['OpenTHC']);
unset($data['menu']);
unset($data['menu0']);

ob_start();

echo 'Lab Result: ' . $data['Result']['guid'] . "\n";
echo 'Lab Sample: ' . $data['Sample']['guid'] . "\n";
echo "\n";
echo str_repeat('-', 72) . "\n";
echo "Cannabinoids\n";
echo str_repeat('-', 72) . "\n";
echo "Terpenes\n";
echo str_repeat('-', 72) . "\n";
echo "Heavy Metals\n";
echo str_repeat('-', 72) . "\n";
echo "Microbes\n";
echo str_repeat('-', 72) . "\n";
echo "Mycotoxins\n";
echo str_repeat('-', 72) . "\n";
echo "Pesticides\n";
echo str_repeat('-', 72) . "\n";
echo "\n";
echo "\n";
echo str_repeat('-', 72) . "\n";
echo "Laboratory: {$data['Laboratory']['name']}\n";
echo "Created: {$data['Result']['created_at']}\n";

echo str_repeat('-', 72) . "\n";
print_r($data);


$output_text = ob_get_clean();
__exit_text($output_text);
