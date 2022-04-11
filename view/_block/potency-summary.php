<?php
/**
 * Potency Result Cards
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

$thc = floatval($data['Lab_Result_Metric_list']['018NY6XC00PXG4PH0TXS014VVW']['qom'] ?: $data['Lab_Result']['thc']);
if ($thc > 0) {
	$uom = \App\UOM::nice($data['Lab_Result_Metric_list']['018NY6XC00PXG4PH0TXS014VVW']['uom'] ?: '%');
	$thc = sprintf('%0.3f %s', $thc, $uom);
} else {
	$thc = '-.--';
}

$cbd = floatval($data['Lab_Result_Metric_list']['018NY6XC00DEEZ41QBXR2E3T97']['qom'] ?: $data['Lab_Result']['cbd']);
// if ($cbd > 0) {
	$uom = \App\UOM::nice($data['Lab_Result_Metric_list']['018NY6XC00DEEZ41QBXR2E3T97']['uom'] ?: '%');
	$cbd = sprintf('%0.3f %s', $cbd, $uom);
// } else {
	// $cbd = '-.--';
// }

$sum = floatval($data['Lab_Result_Metric_list']['018NY6XC00SAE8Q4JSMF40YSZ3']['qom'] ?: $data['Lab_Result']['sum']);
$uom = \App\UOM::nice($data['Lab_Result_Metric_list']['018NY6XC00SAE8Q4JSMF40YSZ3']['uom'] ?: '%');
$sum = sprintf('%0.3f %s', $sum, $uom);
// } else {
// 	$sum = '-.--';
// }

?>

<div class="row card-wrap potency-result-cards">
	<div class="col">
		<div class="card">
			<div class="card-body">
				<h2 class="c" style="margin:0;">THC:
					<span class="potency-result-thc"><?= $thc ?></span>
				</h2>
			</div>
		</div>
	</div>
	<div class="col">
		<div class="card">
			<div class="card-body">
				<h2 class="c" style="margin:0;">CBD:
					<span class="potency-result-cbd"><?= $cbd ?></span>
				</h2>
			</div>
		</div>
	</div>
	<div class="col">
		<div class="card">
			<div class="card-body">
				<h2 class="c" style="margin:0;">Total:
					<span class="potency-result-total"><?= $sum ?></span>
				</h2>
			</div>
		</div>
	</div>
</div>
