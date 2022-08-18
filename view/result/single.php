<?php
/**
 * Render a Lab Result
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

use OpenTHC\Lab\Lab_Result;
use OpenTHC\Lab\UOM;

$dtC = new \DateTime($data['Lab_Result']['created_at']); //, new \DateTimezone($_SESSION['tz']));
$dtC->setTimezone(new \DateTimezone($_SESSION['tz']));

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
		<p>Created: <?= $dtC->format('Y-m-d H:i e') ?>
		<?php
		if ( ! empty($data['Lab_Result']['approved_at'])) {
			$dtA = new \DateTime($data['Lab_Result']['approved_at']);
			$dtA->setTimezone(new \DateTimezone($_SESSION['tz']));
			printf('Approved: %s', $dtA->format('Y-m-d H:i e'));
		}
		if ( ! empty($data['Lab_Result']['expires_at'])) {
			$dtE = new \DateTime($data['Lab_Result']['expires_at']);
			$dtE->setTimezone(new \DateTimezone($_SESSION['tz']));
			printf('Expires: %s', $dtE->format('Y-m-d H:i e'));
		}
		?></p>
	</div>

	<div>
		<h3>Status: <?= _lab_result_status_nice($data['Lab_Result']['stat']) ?></h3>
	</div>

	<div class="r">
		<form method="post">

			<div class="btn-group">
				<?php
				switch ($data['Lab_Result']['stat']) {
					case 0:
					case 100:
					case 102:
					case 200:
						if (0 == ($data['Lab_Result']['flag'] & Lab_Result::FLAG_LOCK)) {
							printf('<a class="btn btn-primary" href="/result/%s/update"><i class="far fa-edit"></i> Edit</a>', $data['Lab_Result']['id']);
							echo '<button class="btn btn-secondary" name="a" value="lab-result-commit"><i class="fa-solid fa-flag-checkered"></i> Commit</button>';
						} else {
							// Nothing
						}
						break;
				}
				?>
				<!-- <button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" type="button"><i class="fas fa-download"></i></button> -->
				<!-- <button class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" type="button"></button> -->
				<!-- <div class="dropdown-menu dropdown-menu-lg-end"> -->
					<!-- <a class="dropdown-item" href="/result/<?= $data['Lab_Result']['id'] ?>/download?f=pdf"><i class="fas fa-download"></i> Download COA (PDF)</a> -->
					<!-- <a class="dropdown-item" href="/result/<?= $data['Lab_Result']['id'] ?>/download?f=png%2Bcoa"><i class="fas fa-download"></i> Download COA (PNG/QR)</a> -->
					<!-- <a class="dropdown-item" href="/result/<?= $data['Lab_Result']['id'] ?>/download?f=csv"><i class="fas fa-download"></i> Download CSV</a> -->
					<!-- <a class="dropdown-item" href="/result/<?= $data['Lab_Result']['id'] ?>/download?f=csv%2Bccrs"><i class="fas fa-download"></i> Download CSV/CCRS</a> -->
					<!-- <a class="dropdown-item" href="/result/<?= $data['Lab_Result']['id'] ?>/download?f=json"><i class="fas fa-download"></i> Download JSON</a> -->
					<!-- <a class="dropdown-item" href="/result/<?= $data['Lab_Result']['id'] ?>/download?f=json%2Bwcia"><i class="fas fa-download"></i> Download JSON/WCIA</a> -->
					<!-- <a class="dropdown-item" href="/result/<?= $data['Lab_Result']['id'] ?>/download?f=png"><i class="fas fa-download"></i> Download PNG</a> -->
				<!-- </div> -->
			</div>

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

?>
	<hr>
	<section>
		<h3><?= $lms['name'] ?></h3>
		<div class="result-metric-wrap">
			<?php
			// Spin too many times but, whatever /djb 20220222
			$out = false;
			foreach ($lms['metric_list'] as $lm_id => $result_data) {

				if (empty($result_data['metric'])) {
					continue;
				}

				$out = true;

				$metric = $result_data['metric'];

				// Special UOM
				switch ($metric['uom']) {
					case 'bool':
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
						<?php
						if ( ! empty($metric['uom'])) {
							printf('<div class="input-group-text">%s</div>', UOM::nice($metric['uom']));
						}
						?>
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

<!--
<form method="post">
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
