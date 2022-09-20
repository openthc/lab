<?php
/**
 * Lab Result Create
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

$stat_pick_html = _draw_stat_pick();
$unit_pick_html = _draw_unit_pick();


?>

<h1><a href="/result">Result</a> :: Create</h1>

<form action="" autocomplete="off" method="post">
<div class="container">
<section style="margin-bottom:1rem;">

	<div class="row">
		<div class="col-md-6">
			<div class="input-group mb-2">
				<div class="input-group-text">
					<a href="/sample/<?= $data['Lab_Sample']['id'] ?>">Sample:</a>
				</div>
				<input class="form-control" readonly value="<?= __h($data['Lab_Sample']['name']) ?>">
			</div>
		</div>
		<div class="col-md-6">
			<div class="input-group mb-2">
				<div class="input-group-text">
					<a href="/report/license?id=<?= $data['License_Source']['id'] ?>">License:</a>
				</div>
				<input class="form-control" readonly value="<?= __h($data['License_Source']['name']) ?>">
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
		<div class="col-md-6">
			<div class="mb-2">
				<div class="input-group">
					<div class="input-group-text">Machine Data:</div>
					<input class="form-control" name="machine-file" type="file">
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="mb-2">
				<div class="input-group">
					<div class="input-group-text">Upstream COA:</div>
					<input class="form-control" name="file" type="file">
				</div>
			</div>
		</div>
	</div>

	<div class="mb-2">
		<div class="input-group">
			<div class="input-group-text">Status:</div>
			<select class="form-control" name="lab-result-stat">
				<option value="100">In Progress</option>
				<option value="200">Passed</option>
				<option value="400">Failed</option>
			</select>
		</div>
	</div>

	<div class="mb-2">
		<div class="input-group">
			<div class="input-group-text">Note:</div>
			<textarea class="form-control" id="lab-result-terp-note" name="terp-note"><?= __h($data['Lab_Result']['note']) ?></textarea>
			<button class="btn btn-outline-secondary btn-terp-note-auto" type="button"><i class="fas fa-magic"></i> Auto</button>
		</div>
	</div>

</section>

<?php
foreach ($data['lab_metric_section_list'] as $lms) {

	$lms_data = $data['Lab_Result_Metric_list'][$lms['stub']];
	if (empty($lms_data)) {
		continue;
	}

?>
	<section style="margin-bottom:1rem;">

		<div class="d-flex justify-content-between">
			<div>
				<h2><?= __h($lms['name']) ?></h2>
			</div>
			<?php
			if ('General' != $lms['name']) {
			?>
				<div><?= $stat_pick_html ?></div>
				<div><?= $unit_pick_html ?></div>
			<?php
			}
			?>
		</div>

		<div class="lab-metric-grid" id="lab-metric-type-<?= $lms['id'] ?>">
		<?php
		foreach ($lms_data as $i => $lm) {
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

<div class="form-actions">
	<input name="sample_id" type="hidden" value="<?= $data['Lab_Sample']['id'] ?>">
	<button class="btn btn-primary" name="a" value="lab-result-save"><i class="fas fa-save"></i> Save</button>
	<button class="btn btn-primary" name="a" value="lab-result-save-and-commit"><i class="fas fa-save"></i> Save &amp; Commit</button>
	<!-- <button class="btn btn-outline-danger" name="a" value="lab-result-delete"><i class="fas fa-save"></i> Delete</button> -->
</div>

</div>
</form>

<script src="/js/result.js"></script>
