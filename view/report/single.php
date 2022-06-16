<?php
/**
 * Render a Lab Report
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

use App\Lab_Result;
use OpenTHC\Lab\Lab_Report;

?>

<div class="container">
<div class="d-flex flex-row flex-wrap justify-content-between mt-2">

	<div>
		<h1>Report: <?= $data['Lab_Report']['name'] ?></h1>
		<h2>Sample: <?php
		if (empty($data['Lab_Sample']['id'])) {
			echo '-orphan-';
		} else {
			printf('<a href="/sample/%s">%s</a></h2>'
				, $data['Lab_Sample']['id']
				, ($data['Lab_Sample']['name'] ?: $data['Lab_Sample']['id'])
			);
		}
		?>
	</div>

	<div>
		<h3>Status: <?= _lab_result_status_nice($data['Lab_Report']['stat']) ?></h3>
	</div>

	<div class="r">
		<form method="post" target="_blank">

			<div class="btn-group">
				<a class="btn btn-primary" href="/report/<?= $data['Lab_Report']['id'] ?>/update"><i class="fa-solid fa-flag-checkered"></i> Commit</a>
				<button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" type="button"><i class="fas fa-download"></i></button>
				<!-- <button class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" type="button"></button> -->
				<div class="dropdown-menu dropdown-menu-lg-end">
					<a class="dropdown-item" href="/report/<?= $data['Lab_Report']['id'] ?>/download?f=pdf"><i class="fas fa-download"></i> Download COA (PDF)</a>
					<!-- <a class="dropdown-item" href="/report/<?= $data['Lab_Report']['id'] ?>/download?f=png%2Bcoa"><i class="fas fa-download"></i> Download COA (PNG/QR)</a> -->
					<!-- <a class="dropdown-item" href="/report/<?= $data['Lab_Report']['id'] ?>/download?f=csv"><i class="fas fa-download"></i> Download CSV</a> -->
					<a class="dropdown-item" href="/report/<?= $data['Lab_Report']['id'] ?>/download?f=csv%2Bccrs"><i class="fas fa-download"></i> Download CSV/CCRS</a>
					<!-- <a class="dropdown-item" href="/report/<?= $data['Lab_Report']['id'] ?>/download?f=json"><i class="fas fa-download"></i> Download JSON</a> -->
					<a class="dropdown-item" href="/report/<?= $data['Lab_Report']['id'] ?>/download?f=json%2Bwcia"><i class="fas fa-download"></i> Download JSON/WCIA</a>
					<!-- <a class="dropdown-item" href="/report/<?= $data['Lab_Report']['id'] ?>/download?f=png"><i class="fas fa-download"></i> Download PNG</a> -->
				</div>
			</div>

			<div class="btn-group">
				<?php
				if ($data['Lab_Report']['flag'] & Lab_Report::FLAG_PUBLIC) {
					echo '<button class="btn btn-outline-success" name="a" title="Lab Reports Published, click to re-publish &amp; view" type="submit" value="lab-report-share"><i class="fas fa-share-alt"></i> Share</button>';
				} else {
					echo '<button class="btn btn-outline-warning" name="a" title="Lab Results NOT Published" type="submit" value="lab-report-share"><i class="fas fa-share-alt"></i> Share</button>';
				}
				?>
				<!-- <a class="btn btn-outline-secondary" href="mailto:?<?= $data['share_mail_link'] ?>"><i class="fas fa-envelope-open-text"></i></a> -->
			</div>

			<?php
			if ($data['Lab_Report']['coa_file']) {
			?>
				<div class="btn-group">
					<button class="btn btn-outline-success" name="a" type="submit" value="coa-download"><i class="fas fa-download"></i> COA</button>
					<!-- <button class="btn btn-outline-success dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" type="button"></button> -->
					<button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modal-coa-upload" title="Upload COA" type="button"><i class="fas fa-upload"></i></button>
					<!-- <button class="btn btn-outline-secondary" name="a" type="submit" value="coa-create"><i class="fas fa-print"></i></button> -->
				</div>
			<?php
			} else {
			?>
				<div class="btn-group" id="dropzone-coa-upload">
					<button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modal-coa-upload" name="a" title="No COA Uploaded" type="button" value="download-coa"><i class="fas fa-upload"></i> COA</button>
					<!-- <button class="btn btn-outline-secondary" name="a" type="submit" value="coa-create"><i class="fas fa-print"></i></button> -->
				</div>
			<?php
			}
			?>
		</form>
	</div>

</div>

<div class="mb-2">
<?= $this->block('product-summary.php') ?>
</div>

<div class="mb-2">
<!-- Depends on Lab_Result_Metric_list -->
<?= $this->block('potency-summary.php') ?>
</div>

<div>
<?php
$Result_Metric_By_Type_list = [];
$lab_metric_list = $data['Lab_Report']['meta']['lab_metric_list'];
foreach ($lab_metric_list as $lm1) {

	$lmtid = $lm1['lab_metric_type_id'];
	$lmt = $data['lab_metric_type_list'][ $lmtid ];

	if (empty($Result_Metric_By_Type_list[ $lmtid ])) {
		$lmt['metric_list'] = [];
		$Result_Metric_By_Type_list[ $lmtid ] = $lmt;
	}

	$lm0 = $data['lab_metric_list'][ $lm1['lab_metric_id'] ];
	$lm0['metric'] = $lm1;

	$Result_Metric_By_Type_list[ $lmtid ]['metric_list'][] = $lm0;
}

foreach ($data['lab_metric_type_list'] as $lmt) {

	$lms = $Result_Metric_By_Type_list[ $lmt['id'] ];

	if (empty($lms['metric_list'])) {
		echo '<!--';
		printf('<div class="alert alert-secondary">%s: No Metrics</div>', $lmt['name']);
		echo '-->';
		continue;
	}

	if (empty($lms['name'])) {
		$lms['name'] = $lms['id'] ?: '-orphan-';
	}

?>
	<hr>
	<section>
		<h3><?= $lmt['name'] ?></h3>
		<div class="result-metric-wrap">
			<?php
			// Spin too many times but, whatever /djb 20220222
			foreach ($lms['metric_list'] as $lm_id => $result_data) {

				if (empty($result_data['metric'])) {
					continue;
				}

				$out = true;

				$metric = $result_data['metric'];

				switch ($metric['uom']) {
					case 'bool':
						// Something
						$metric['uom'] = '';
						switch ($metric['qom']) {
							case 0:
								$metric['qom'] = 'Fail';
								break;
							case 1:
								$metric['qom'] = 'Pass';
								break;
						}
						break;
				}

				// Special QOM
				switch ($metric['qom']) {
					case -1:
						$metric['qom'] = 'N/A';
						$metric['uom'] = '';
						break;
					case -2:
						$metric['qom'] = 'N/D';
						$metric['uom'] = '';
						break;
					case -3:
						$metric['qom'] = 'N/T';
						$metric['uom'] = '';
						break;
					case -130:
						$metric['qom'] = 'T/D';
						$metric['uom'] = '';
						break;
				}
			?>
				<div class="result-metric-data" data-metric-id="<?= $metric['lab_metric_id'] ?>">
					<div class="input-group">
						<div class="input-group-text"><?= __h($result_data['name']) ?></div>
						<input class="form-control r" readonly style="font-weight: bold;" value="<?= __h($metric['qom']) ?>">
						<div class="input-group-text"><?= App\UOM::nice($metric['uom']) ?></div>
					</div>
				</div>
			<?php
			}

			if (!$out) {
				echo '<div class="alert alert-info" style="flex: 1 1 auto; width: 100%;">No Metrics for this Section</div>';
			}

			?>
		</div>
	</section>
<?php
}
?>
</div>

</div>

<?= $this->block('modal-coa-upload.php') ?>
<?= $this->block('modal-send-email.php') ?>

<!--
<pre>
<?php
print_r($data['Lab_Report']);
?>
</pre>
-->