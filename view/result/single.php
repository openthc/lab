<?php
/**
 * Render a Lab Result
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

use OpenTHC\Lab\Lab_Metric;
use OpenTHC\Lab\Lab_Result;
use OpenTHC\Lab\Lab_Result_Metric;
use OpenTHC\Lab\UOM;

?>

<div class="container">
<div class="d-flex flex-row flex-wrap justify-content-between mt-2">

	<div class="report-header">
		<h1>Result: <?= $data['Lab_Result']['guid'] ?></h1>
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
		<h3>Status: <?= _lab_result_status_nice($data['Lab_Result']['stat']) ?></h3>
	</div>

	<div class="r">
		<form autocomplete="off" method="post">

			<div class="btn-group">
				<?php
				switch ($data['Lab_Result']['stat']) {
					case 0:
					case Lab_Result::STAT_OPEN:
					case Lab_Result::STAT_WAIT:
					case Lab_Result::STAT_PASS:
						if (0 == ($data['Lab_Result']['flag'] & Lab_Result::FLAG_LOCK)) {
							echo '<button class="btn btn-primary" name="a" value="lab-result-commit"><i class="fa-solid fa-flag-checkered"></i> Commit</button>';
							printf('<a class="btn btn-secondary" href="/result/%s/update"><i class="far fa-edit"></i> Edit</a>', $data['Lab_Result']['id']);
						} else {
							// Nothing
						}
						break;
				}
				?>
				<button class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" type="button"><i class="fas fa-download"></i></button>
				<div class="dropdown-menu dropdown-menu-lg-end">
					<a class="dropdown-item" href="/result/<?= $data['Lab_Result']['id'] ?>/download?f=pdf"><i class="fas fa-download"></i> Download COA (PDF)</a>
					<a class="dropdown-item" href="/result/<?= $data['Lab_Result']['id'] ?>/download?f=png%2Bcoa"><i class="fas fa-download"></i> Download COA (PNG/QR)</a>
					<!-- <a class="dropdown-item" href="/result/<?= $data['Lab_Result']['id'] ?>/download?f=csv"><i class="fas fa-download"></i> Download CSV</a> -->
					<a class="dropdown-item" href="/result/<?= $data['Lab_Result']['id'] ?>/download?f=csv%2Bccrs"><i class="fas fa-download"></i> Download CSV/CCRS</a>
					<a class="dropdown-item" href="/result/<?= $data['Lab_Result']['id'] ?>/download?f=json"><i class="fas fa-download"></i> Download JSON</a>
					<a class="dropdown-item" href="/result/<?= $data['Lab_Result']['id'] ?>/download?f=json%2Bwcia"><i class="fas fa-download"></i> Download JSON/WCIA</a>
					<!-- <a class="dropdown-item" href="/result/<?= $data['Lab_Result']['id'] ?>/download?f=png"><i class="fas fa-download"></i> Download PNG</a> -->
				</div>
			</div>

			<?php
			// Only non-Laboratories can share Result, Lab must move it forward to a Report
			if ('Laboratory' != $_SESSION['License']['type']) {
			?>
				<div class="btn-group">
					<?php
					if ($data['Lab_Result']['flag'] & Lab_Result::FLAG_PUBLIC) {
						echo '<button class="btn btn-outline-success" name="a" title="Lab Report Published, click to re-publish &amp; view" type="submit" value="lab-result-share"><i class="fas fa-share-alt"></i> Share</button>';
					} else {
						echo '<button class="btn btn-warning" name="a" title="Lab Report NOT Published" type="submit" value="lab-result-share"><i class="fas fa-share-alt"></i> Share</button>';
					}
					?>
					<!-- <a class="btn btn-outline-secondary" href="mailto:?<?= $data['share_mail_link'] ?>"><i class="fas fa-envelope-open-text"></i></a> -->
				</div>
			<?php
			}

			// COA File for Result
			if ($data['Lab_Result']['coa_file']) {
			?>
				<div class="btn-group">
					<button class="btn btn-outline-primary" name="a" type="submit" value="coa-download"><i class="fas fa-download"></i> COA</button>
					<!-- <button class="btn btn-outline-success dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" type="button"></button> -->
					<button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modal-coa-upload" title="Upload COA" type="button"><i class="fas fa-upload"></i></button>
					<!-- <button class="btn btn-outline-secondary" name="a" type="submit" value="coa-create"><i class="fas fa-print"></i></button> -->
				</div>
			<?php
			} else {
			?>
				<div class="btn-group" id="dropzone-coa-upload">
					<button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modal-coa-upload" name="a" title="No COA Uploaded" type="button" value="download-coa"><i class="fas fa-upload"></i> COA</button>
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
<?= $this->block('potency-summary.php') ?>
</div>


<div>
<?php
foreach ($data['Result_Metric_Group_list'] as $lms) {

	if (empty($lms['metric_list'])) {
		continue;
	}

	if (empty($lms['name'])) {
		$lms['name'] = '-system-';
		// var_dump($lms);
	}

	$lms_status = $data['Lab_Result']['meta']['lab_metric_type_list'][ $lms['id'] ]['stat'];

	// Spin too many times but, whatever /djb 20220222
	$out_metric_list = [];
	foreach ($lms['metric_list'] as $lm_id => $result_data) {

		if (empty($result_data['metric'])) {
			continue;
		}

		$metric = $result_data['metric'];
		$metric['name'] = $result_data['name'];

		$out_metric_list[] = \OpenTHC\Lab\UI\Lab_Result_Metric::input_group($metric);

	}

	if (empty($out_metric_list)) {
		// echo '<div class="alert alert-info" style="flex: 1 1 auto; width: 100%;">No Metrics for this Section</div>';
		continue;
	}

	switch ($lms_status) {
		case Lab_Result_Metric::STAT_OPEN:
			$lms_status = 'In Progress';
			break;
		case Lab_Result_Metric::STAT_PASS:
			$lms_status = '<strong class="text-success">Passed</strong>';
			break;
		case Lab_Result_Metric::STAT_FAIL: // v1
			$lms_status = '<strong class="text-danger">Failed</strong>';
			break;
		default:
			$lms_status = sprintf('<span class="text-danger">%d?</span>', $lms_status);
			break;
	}


?>

	<hr>

	<section>
		<div class="d-flex justify-content-between">
			<div><h3><?= $lms['name'] ?></h3></div>
			<div><?= $lms_status ?></div>
		</div>
		<div class="result-metric-wrap">
			<?= implode('', $out_metric_list); ?>
		</div>
	</section>

<?php
}
?>
</div>

<!--
<form autocomplete="off" method="post">
<div class="form-actions">
	<button class="btn btn-outline-secondary" name="a" type="submit" value="sync"><i class="fas fa-sync"></i> Sync</button>
	<button class="btn btn-outline-primary" name="a" type="submit" value="save"><i class="fas fa-save"></i> Modify</button>
	<button class="btn btn-outline-secondary" name="a" type="submit" value="mute"><i class="fas fa-ban"></i> Mute</button>
	<button class="btn btn-outline-danger" name="a" type="submit" value="void"><i class="fas fa-trash"></i> Void</button>
</div>
</form>
-->

</div>

<?= $this->block('modal-coa-upload.php') ?>
<?= $this->block('modal-send-email.php') ?>


<script>
$(function() {

	$('#dropzone-coa-upload').on('dragover', function(e) {
		// $('.upload-drop-zone .progress-bar').css('width', '0%').text('0%');
		// $(this).addClass('active');
		// return false;
	});

	$('#dropzone-coa-upload').on('dragleave', function(e) {
		$(this).removeClass('active');
	});

	$('#dropzone-coa-upload').on('drop', function(e) {

		// Upload COA
		$('#dropzone-coa-upload .btn').removeClass('btn-primary').addClass('btn-outline-warning');

		var e0 = e.originalEvent;

		const type_list = e0.dataTransfer.types;
		console.log('dropped', type_list);

		// Did we drop a file?
		if (type_list.includes('Files')) {
			var form = new FormData();
			form.set('a', 'coa-upload');
			form.append('file', e0.dataTransfer.files[0]);
			fetch('', {
				method: 'POST',
				body: form,
			}).then(res => {
				if (res.redirected) {
					window.location = res.url;
					return false;
				}
				return res.json();
			}).then(json => {
				// debugger;
				// alert(json);
			});
		}

		// Did we drop a link?
		if (type_list.includes('text/uri-list')) {
			const drop_link = e0.dataTransfer.getData('text/uri-list');
			if (drop_link) {
				$('#drop-data-target').val( drop_link );
				$('#b2b-incoming-next').val('b2b-incoming-import');
				$('#b2b-incoming-next').click();
			}
			return;
		}

	});

	// Prevent accidental drops
	$(document).on('drop dragover', function (e) {
		$('#dropzone-coa-upload .btn').removeClass('btn-outline-warning').addClass('btn-primary');
		e.preventDefault();
	});
	$(document).on('dragleave', function() {
		$('#dropzone-coa-upload .btn').removeClass('btn-primary').addClass('btn-outline-warning');
	});

});
</script>
