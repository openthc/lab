<?php
/**
 * Lab Result Update
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

$stat_pick_html = _draw_stat_pick();
$unit_pick_html = _draw_unit_pick();

?>

<h1><a href="/result">Result</a> :: <?= $data['Lab_Result']['guid'] ?> :: Update</h1>

<form action="" autocomplete="off" enctype="multipart/form-data" method="post">
<div class="container">
<section style="margin-bottom:1rem;">

	<div class="row">
		<div class="col-md-6">
			<div class="input-group mb-2">
				<div class="input-group-text">
					<a href="/sample/<?= $data['Lab_Sample']['id'] ?>">Sample:</a>
				</div>
				<input class="form-control" readonly value="<?= __h($data['Lab_Sample']['name']) ?>">
				<button class="btn btn-outline-secondary" type="button"><i class="far fa-edit"></i></button>
			</div>
		</div>
		<div class="col-md-6">
			<div class="input-group mb-2">
				<?php
				if (empty($data['License_Source']['id'])) {
					echo '<div class="input-group-text">';
					echo '<span class="text-warning">License:</span>';
					echo '</div>';
					echo '<input class="form-control" readonly value="-orphan-">';
				} else {
					echo '<div class="input-group-text">';
					printf('<a href="/report/license?id=%s">License:</a>', $data['License_Source']['id']);
					echo '</div>';
					printf('<input class="form-control" readonly value="%s">', __h($data['License_Source']['name']));
				}
				?>
			</div>
		</div>
	</div>


	<div class="row">
		<div class="col-md-4">
			<div class="input-group mb-2">
				<div class="input-group-text">Lot:</div>
				<input class="form-control" readonly value="<?= __h($data['Lot']['guid']) ?>">
			</div>
		</div>
		<div class="col-md-4">
			<div class="input-group mb-2">
				<div class="input-group-text">Product:</div>
				<input class="form-control" readonly value="<?= __h($data['Product']['name']) ?> [<?= basename($data['Product_Type']['name']) ?>]">
			</div>
		</div>
		<div class="col-md-4">
			<div class="input-group mb-2">
				<div class="input-group-text">Variety:</div>
				<input class="form-control" readonly value="<?= __h($data['Variety']['name']) ?>">
			</div>
		</div>
	</div>

	<div class="row">

		<h3><i class="far fa-comments"></i> Note:</h3>
		<div class="input-group">
			<textarea class="form-control" id="lab-result-terp-note" name="terp-note"><?= __h($data['Lab_Result']['note']) ?></textarea>
			<button class="btn btn-outline-secondary btn-terp-note-auto" type="button"><i class="fas fa-magic"></i> Auto</button>
		</div>

	</div>

</section>

<?php
foreach ($data['Result_Metric_Group_list'] as $lms_id => $lms) {
?>
	<section style="margin-bottom:1rem;">

		<div class="d-flex justify-content-between">
			<div>
				<h2><?= __h($lms['name']) ?></h2>
			</div>
			<?php
			if ('General' != $lms['name']) {
			?>
				<div>
					<div class="btn-group btn-group-sm">
						<button class="btn btn-outline-secondary lab-metric-qom-bulk" type="button" value="OK">OK</button>
						<button class="btn btn-outline-secondary lab-metric-qom-bulk" type="button" value="N/A">N/A</button>
						<button class="btn btn-outline-secondary lab-metric-qom-bulk" type="button" value="N/D">N/D</button>
						<button class="btn btn-outline-secondary lab-metric-qom-bulk" type="button" value="N/T">N/T</button>
					</div>
				</div>
				<div>
					<?= $unit_pick_html ?>
				</div>
			<?php
			}
			?>
		</div>

		<div class="lab-metric-grid" id="lab-metric-type-<?= $lms['id'] ?>">
		<?php
		foreach ($lms['metric_list'] as $lm_id => $lm) {
			switch ($lm['meta']['uom']) {
			case 'bool':
				_draw_metric_select_pass_fail($lm);
				break;
			default:
				_draw_metric($lm);
				break;
			}
		}
		?>
		</div>
	</section>
	<hr>
<?php
}
?>


<div class="mb-2">
	<div class="input-group">
		<div class="input-group-prepend">
			<div class="input-group-text">COA File:</div>
		</div>
		<input class="form-control" name="file" type="file">
	</div>
</div>

<div class="mb-2">
	<div class="input-group">
		<div class="input-group-prepend">
			<div class="input-group-text">Status:</div>
		</div>
		<select class="form-control" name="lab-result-stat">
			<option value="100">In Progress</option>
			<option value="200">Passed</option>
			<option value="400">Failed</option>
		</select>
	</div>
</div>

<div class="form-actions">
	<input name="sample_id" type="hidden" value="<?= $data['Lab_Sample']['id'] ?>">
	<button class="btn btn-primary" name="a" value="lab-result-save"><i class="fas fa-save"></i> Save</button>
	<button class="btn btn-secondary" name="a" value="lab-result-commit"><i class="fa-solid fa-flag-checkered"></i> Commit</button>
	<button class="btn btn-outline-danger" name="a" value="lab-result-delete"><i class="fas fa-save"></i> Delete</button>
</div>

</div>
</form>

<script src="/js/result.js"></script>
