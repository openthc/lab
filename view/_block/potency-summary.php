<?php
/**
 * Potency Result Cards
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

?>

<div class="row card-wrap potency-result-cards">
	<div class="col">
		<div class="card">
			<div class="card-body">
				<h2 class="c" style="margin:0;">THC:
					<span class="potency-result-thc"><?= ($data['MetricList']['Cannabinoid']['018NY6XC00PXG4PH0TXS014VVW']['qom'] ?: $data['Result']['thc']) ?></span>
				</h2>
			</div>
		</div>
	</div>
	<div class="col">
		<div class="card">
			<div class="card-body">
				<h2 class="c" style="margin:0;">CBD:
					<span class="potency-result-cbd"><?= ($data['MetricList']['Cannabinoid']['018NY6XC00DEEZ41QBXR2E3T97']['qom'] ?: $data['Result']['cbd']) ?></span>
				</h2>
			</div>
		</div>
	</div>
	<div class="col">
		<div class="card">
			<div class="card-body">
				<h2 class="c" style="margin:0;">Total:
					<span class="potency-result-total"><?= ($data['MetricList']['Cannabinoid']['018NY6XC00SAE8Q4JSMF40YSZ3']['qom'] ?: $data['Result']['sum']) ?></span>
				</h2>
			</div>
		</div>
	</div>
</div>
