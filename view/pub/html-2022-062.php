<?php
/**
 * Public View of a Lab Result
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

use OpenTHC\Lab\UOM;

$lab_result_metric = $data['Lab_Result_Metric_list'];
$lab_result_section_metric = $data['Lab_Result_Section_Metric_list'];


?>

<style>
.metric-general-wrap h2 {
	background: #343434;
	color: #fefefe;
	margin: 0 0 0.50rem 0;
	padding: 0.25rem 0.50rem;
}

.metric-general-wrap .col {
	text-align: center;
}

/* Backported? */
.metric-section {
	flex: 1 1 50%;
	margin: 0;
	padding: 0 0.50rem 1rem 0.50rem;
	min-width: 20em;
}

</style>


<div class="container mt-4" id="pub-html-2022-062">

<h1>Result: <?= __h($data['Lab_Result']['guid']) ?></h1>
<h2>Sample: <?= __h($data['Lab_Sample']['name'] ?: $data['Lab_Sample']['guid'] ?: '-orphan-') ?></h2>

<?= $this->block('product-summary.php') ?>

<div class="row">
<div class="col-md-8">
	<div class="mb-2">
		<label>Share Link</label>
		<div class="input-group">
			<input class="form-control" readonly type="url" value="https://<?= $data['Site']['hostname'] ?>/pub/<?= $data['Lab_Result']['id'] ?>.html">
			<button class="btn btn-outline-secondary btn-clipcopy"
				data-clipboard-text="https://<?= $data['Site']['hostname'] ?>/pub/<?= $data['Lab_Result']['id'] ?>/wcia.json"
				type="button"><i class="fas fa-clipboard"></i></button>
			<button class="btn btn-outline-secondary dropdown-toggle"
				data-bs-toggle="dropdown"
				type="button"></button>
			<ul class="dropdown-menu dropdown-menu-end">
				<li><a class="dropdown-item" href="https://<?= $data['Site']['hostname'] ?>/pub/<?= $data['Lab_Result']['id'] ?>.txt">TXT</a></li>
				<li><a class="dropdown-item" href="https://<?= $data['Site']['hostname'] ?>/pub/<?= $data['Lab_Result']['id'] ?>.json">JSON</a></li>
				<li><a class="dropdown-item" href="https://<?= $data['Site']['hostname'] ?>/pub/<?= $data['Lab_Result']['id'] ?>/wcia.json">WCIA</a></li>
				<li><a class="dropdown-item" href="https://<?= $data['Site']['hostname'] ?>/pub/<?= $data['Lab_Result']['id'] ?>/ccrs.txt">CCRS</a></li>
			</ul>
		</div>
	</div>
</div>
<div class="col-md-4">
	<div class="mb-2">
		<label>Print Link
			<span data-toggle="tooltip" data-placement="top" style="cursor:help;" title="Waiting for the Product Owner or Laboratory to upload these documents">
				<i class="fas fa-info-circle"></i>
			</span>
		</label>
		<div class="input-group">
			<?php
			if ($data['Lab_Result']['coa_file']) {
			?>
				<a class="btn btn-block btn-outline-primary v33" href="https://<?= $data['Site']['hostname'] ?>/pub/<?= $data['Lab_Result']['id'] ?>.pdf" target="_blank"><i class="fas fa-print"></i> Print COA</a>
			<?php
			} else {
			?>
				<div class="btn btn-block btn-outline-secondary disabled"><i class="fas fa-print"></i> Waiting for Documents</div>
			<?php
			}
			?>
		</div>
	</div>
</div>
</div>

<?= $this->block('potency-summary.php') ?>

<hr>

<div class="d-flex flex-row flex-wrap" style="margin-bottom: 1rem;">

	<div class="metric-section">
		<h3>General</h3>
		<?= _draw_metric_info_table($data['Lab_Result_Section_Metric_list']['018NY6XC00LMT0BY5GND653C0C']['metric_list'], $lab_result_metric); ?>
	</div>

	<div class="metric-section">
		<h3>Cannabinoid Profile</h3>
		<?= _draw_metric_info_table($data['Lab_Result_Section_Metric_list']['018NY6XC00LMT0HRHFRZGY72C7']['metric_list'], $lab_result_metric); ?>
	</div>

	<div class="metric-section">
		<h3>Terpene Profile</h3>
		<?= _draw_metric_info_table($data['Lab_Result_Section_Metric_list']['018NY6XC00LMT07DPNKHQV2GRS']['metric_list'], $lab_result_metric); ?>
	</div>

	<div class="metric-section">
		<h3>Pesticides</h3>
		<?= _draw_metric_info_table($data['Lab_Result_Section_Metric_list']['018NY6XC00LMT09ZG05C2NE7KX']['metric_list'], $lab_result_metric); ?>
	</div>

	<div class="metric-section">
		<h3>Metals</h3>
		<?= _draw_metric_info_table($data['Lab_Result_Section_Metric_list']['018NY6XC00LMT0V6XE7P0BHBCR']['metric_list'], $lab_result_metric); ?>
	</div>

	<div class="metric-section">
		<h3>Microbes</h3>
		<?= _draw_metric_info_table($data['Lab_Result_Section_Metric_list']['018NY6XC00LMT0B7NMK7RGYAMN']['metric_list'], $lab_result_metric); ?>
	</div>

	<div class="metric-section">
		<h3>Mycotoxin</h3>
		<?= _draw_metric_info_table($data['Lab_Result_Section_Metric_list']['018NY6XC00LMT0GDBPF0V9B71Z']['metric_list'], $lab_result_metric); ?>
	</div>

	<div class="metric-section">
		<h3>Solvents</h3>
		<?= _draw_metric_info_table($data['Lab_Result_Section_Metric_list']['018NY6XC00LMT0AQAMJEDSD0NW']['metric_list'], $lab_result_metric); ?>
	</div>

</div>

<!-- <div class="form-actions">
	<button class="btn btn-outline-primary" name="a" data-bs-toggle="modal" data-bs-target="#modal-result-email" type="button"><i class="far fa-envelope"></i> Email</button>
	<button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-scan-qr" type="button"><i class="fas fa-qrcode"></i> QR Code</button>
	<a class="btn btn-outline-secondary" href="https://<?= $data['Site']['hostname'] ?>/pub/<?= $data['Lab_Result']['id'] ?>.json"> JSON</a>
</div> -->

</div>


<?php
/**
 *
 */
function _draw_metric_info_table($metric_list, $lab_result_metric)
{
	// $lrm = $data['Lab_Result_Metric_list'][$mk0];

	if (empty($metric_list)) {
		return '<div class="alert alert-info">No Data</div>';
	}

	// Sort the Metric List
	uasort($metric_list, function($a, $b) {
		$r = $a['sort'] - $b['sort'];
		if (0 == $r) {
			$r = strcmp($a['name'], $b['name']);
		}
		return $r;
	});


	$out = [];
	foreach ($metric_list as $lm) {

		$lrm = $lab_result_metric[ $lm['id'] ];

		// Not sure if this is right
		if ( ! empty($lrm['metric'])) {
			$lrm = $lrm['metric'];
		}

		$out[] = [
			'lab_metric_id' => $lrm['id'],
			'name' => $lrm['name'],
			'qom' => $lrm['qom'],
			'uom' => $lrm['uom'],
		];
	}

	if (empty($out)) {
		return '<div class="alert alert-info">No Data</div>';
	}


	ob_start();
?>
	<table class="table table-sm">
		<tbody>
			<?php
			foreach ($out as $k => $v) {
				printf('<tr id="lab-metric-%s">', $v['lab_metric_id']);
				switch ($v['qom']) {
					case -1:
						printf('<td>%s</td><td class="r">N/A</td>', __h($v['name']));
						break;
					case -2:
						printf('<td>%s</td><td class="r">N/D</td>', __h($v['name']));
						break;
					case -3:
						printf('<td>%s</td><td class="r">N/T</td>', __h($v['name']));
						break;
					default:
						switch ($v['uom']) {
							case 'bool':
								switch ($v['qom']) {
									case 1:
										$v['qom'] = 'Pass';
										break;
									case 0:
										$v['qom'] = 'Fail';
										break;
								}
								// $v['qom'] = // Map Number to Thing
								printf('<td>%s</td><td class="r">%s</td>', __h($v['name']), $v['qom']);
								break;
							case 'pct':
								printf('<td>%s</td><td class="r">%0.3f %s</td>', __h($v['name']), $v['qom'], UOM::nice($v['uom']));
								break;
							default:
								printf('<td>%s</td><td class="r">%0.3f %s</td>', __h($v['name']), $v['qom'], UOM::nice($v['uom']));
								break;
						}
				}
				echo '</tr>';
			}
			?>
		</tbody>
	</table>

	<?php

	return ob_get_clean();

}
