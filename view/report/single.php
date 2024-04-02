<?php
/**
 * Render a Lab Report
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

use OpenTHC\Lab\Lab_Result;
use OpenTHC\Lab\Lab_Report;
use OpenTHC\Lab\UOM;

?>

<div class="container">
<div class="d-flex flex-row flex-wrap justify-content-between mt-2">

	<div class="report-header">
		<h1><?= $data['Lab_Report']['name'] ?></h1>
		<h2>Sample: <?php
		if (empty($data['Lab_Sample']['id'])) {
			echo '-orphan-';
		} else {
			printf('<a href="/sample/%s">%s</a></h2>'
				, $data['Lab_Sample']['id']
				, ($data['Lab_Sample']['name'] ?: $data['Lab_Sample']['id'])
			);
		}
		?></h2>
	</div>

	<div>
		<h3>Status: <?= _lab_result_status_nice($data['Lab_Report']['stat']) ?></h3>
	</div>

	<div class="r">
		<form autocomplete="off" method="post">

			<div class="btn-group">
				<?php
				switch ($data['Lab_Report']['stat']) {
					case 100:
						echo '<button class="btn btn-primary" name="a" value="lab-report-commit"><i class="fa-solid fa-flag-checkered"></i> Commit</button>';
						break;
					default:
						if ($data['Lab_Report']['flag'] & Lab_Report::FLAG_PUBLIC) {
							echo '<button class="btn btn-outline-success" name="a" title="Lab Reports Published, click to re-publish &amp; view" type="submit" value="lab-report-share"><i class="fas fa-share-alt"></i> Share</button>';
						} else {
							echo '<button class="btn btn-primary" name="a" title="Lab Results NOT Published" type="submit" value="lab-report-share"><i class="fas fa-share-alt"></i> Share</button>';
						}
						break;
				}
				?>
				<button class="btn btn-danger" name="a" value="lab-report-delete"><i class="fa-solid fa-trash"></i> DELETE</button>
				<button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" type="button"><i class="fas fa-download"></i></button>
				<!-- <button class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" type="button"></button> -->
				<ul class="dropdown-menu dropdown-menu-lg-end">
					<li class="dropdown-item d-flex justify-content-between">
						<?php
						$link = sprintf('/report/%s/download?f=pdf', $data['Lab_Report']['id']);
						?>
						<a class="btn btn-sm" href="<?= $link ?>" title="Open the Generated COA">COA (PDF) <i class="fas fa-print"></i></a>
						<div class="btn-group btn-group-sm">
							<a class="btn" download href="<?= $link ?>" title="Download the Generated COA"><i class="fas fa-download"></i></a>
							<button class="btn btn-clipcopy" data-clipboard-text="<?= $link ?>" title="Copy Link to the Generated COA" type="button" ><i class="fa-regular fa-clipboard"></i></button>
						</div>
					</li>
					<li class="dropdown-item d-flex justify-content-between">
						<?php
						$link = sprintf('/report/%s/download?f=csv%%2Bccrs', $data['Lab_Report']['id']);
						$link_pub = sprintf('https://%s/pub/%s/ccrs.csv', $_SERVER['SERVER_NAME'], $data['Lab_Report']['id']);
						?>
						<a class="btn btn-sm" href="<?= $link ?>" title="View the CCRS style CSV File">CSV/CCRS <i class="fas fa-file-csv"></i></a>
						<div class="btn-group btn-group-sm">
							<a class="btn" download href="<?= $link ?>" title="Download the CCRS style CSV File"><i class="fas fa-download"></i></a>
							<button class="btn btn-clipcopy" data-clipboard-text="<?= $link_pub ?>"  title="Copy Link to Public CCRS style CSV File" type="button" ><i class="fa-regular fa-clipboard"></i></button>
						</div>
					</li>
					<li class="dropdown-item d-flex justify-content-between">
						<?php
						$link = sprintf('/report/%s/download?f=json%%2Bwcia', $data['Lab_Report']['id']);
						$link_pub = sprintf('https://%s/pub/%s/wcia.json', $_SERVER['SERVER_NAME'], $data['Lab_Report']['id']);
						?>
						<a class="btn btn-sm" href="<?= $link ?>">JSON/WCIA <i class="fa-solid fa-code"></i></a>
						<div class="btn-group btn-group-sm">
							<a class="btn" download href="<?= $link ?>"><i class="fas fa-download"></i></a>
							<button class="btn btn-clipcopy" data-clipboard-text="<?= $link_pub ?>" type="button" ><i class="fa-regular fa-clipboard"></i></button>
						</div>
					</li>
					<!-- <a class="dropdown-item" href="<?= $link ?>?f=png%2Bcoa"><i class="fas fa-download"></i> Download COA (PNG/QR)</a> -->
					<!-- <a class="dropdown-item" href="<?= $link ?>?f=csv"><i class="fas fa-download"></i> Download CSV</a> -->
					<!-- <a class="dropdown-item" href="<?= $link ?>?f=json"><i class="fas fa-download"></i> Download JSON</a> -->
					<!-- <a class="dropdown-item" href="<?= $link ?>?f=png"><i class="fas fa-download"></i> Download PNG</a> -->
				</ul>
			</div>

			<div class="btn-group">
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
<?= $this->block('lab-result-date-row.php') ?>
</div>

<div class="mb-2">
<?= $this->block('product-summary.php') ?>
</div>

<div class="mb-2">
<!-- Depends on Lab_Result_Metric_list -->
<?= $this->block('potency-summary.php') ?>
</div>

<?php
if ( ! empty($data['lab_report_file_list'])) {
	$public_link_list = $data['Lab_Report']['meta']['public_link_list'];
	?>
	<section class="mb-2" id="lab-report-file-list">
	<table class="table table-sm">
		<thead class="table-dark">
			<tr>
				<th>File</th>
				<th>Type</th>
				<th class="r">Size</th>
				<th class="r">Stat</th>
				<th class="r">Actions</th>
			</tr>
		</thead>
	<tbody>
	<?php
	foreach ($data['lab_report_file_list'] as $f) {
		$pub_link = $public_link_list[ $f['id'] ];
		$int_link = sprintf('/report/%s/download/%s', $data['Lab_Report']['id'], $f['id']);
		?>
		<tr>
		<td><a href="<?= $int_link ?>"><?= __h($f['name']) ?></a></td>
		<?php
		printf('<td>%s</td>', __h($f['type']));
		printf('<td class="r">%d</td>', __h($f['size']));
		printf('<td class="r">%d</td>', __h($f['stat']));
		// printf('<td class="r">%08x</td>', __h($f['flag']));
		?>
		<td class="r">
		<div class="btn-group btn-group-sm">
			<?php
			if (empty($pub_link['link'])) {
				echo '<button class="btn btn-outline-secondary disabled"><i class="fa-solid fa-ban"></i></button>';
			} else {
				echo sprintf('<a class="btn btn-sm btn-primary" href="%s" target="_blank"><i class="fa-regular fa-share-from-square"></i></a>', $pub_link['link'], $pub_link['name']);
			}
			?>
			<a class="btn btn-outline-secondary" download href="<?= $int_link ?>"><i class="fas fa-download"></i></a>
			<button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"></button>
			<ul class="dropdown-menu dropdown-menu-end">
				<li><button class="dropdown-item btn-clipcopy"
						data-clipboard-text="<?= $link ?>"
						title="Copy Link"><i class="fa-regular fa-clipboard"></i> Copy Link</button>
				</li>
			</ul>
		</div>
		</td>
		</tr>
	<?php
	}
	?>
	</tbody>
	</table>
	</section>
<?php
}
?>

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

	$Result_Metric_By_Type_list[ $lmtid ]['metric_list'][ $lm0['id'] ] = $lm0;
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

	// Spin too many times but, whatever /djb 20220222
	$out_metric_list = [];
	foreach ($lms['metric_list'] as $lm_id => $result_data) {
	// foreach ($data['lab_metric_list'] as $lm0) {
		// $result_data = $lms['metric_list'][ $lm0['id'] ];

		if (empty($result_data['metric'])) {
			continue;
		}

		$metric = $result_data['metric'];
		$metric['name'] = $result_data['name'];

		$out_metric_list[] = \OpenTHC\Lab\UI\Lab_Result_Metric::input_group($metric);
	}


?>
	<hr>

	<section>
		<h3><?= $lmt['name'] ?></h3>
		<div class="result-metric-wrap">
			<?php
			if (empty($out_metric_list)) {
				echo '<div class="alert alert-info">No Metrics for this Section</div>';
			} else {
				echo implode('', $out_metric_list);
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
