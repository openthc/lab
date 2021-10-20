<?php
/**
 * Create a Lab Result
 */

?>

<div class="row">
<div class="col-md-9">
	<h1>Sample: <?= $data['Sample']['id_nice'] ?></h1>
	<h2>Origin: <?= $data['Sample']['global_original_id'] ?></h2>
</div>
<div class="col-md-3 r">
	<form method="post" target="_blank">
		<?php
		if ($data['Result']['coa_file']) {
		?>
			<div class="btn-group">
				<button class="btn btn-outline-success" name="a" type="submit" value="coa-download"><i class="fas fa-download"></i> COA</button>
				<button class="btn btn-outline-success dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" type="button"></button>
				<div class="dropdown-menu dropdown-menu-right">
					<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modal-coa-upload" href="#"><i class="fas fa-upload"></i> Upload COA</a>
				</div>
			</div>
		<?php
		} else {
		?>
			<div class="btn-group">
				<button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modal-coa-upload" name="a" title="No COA Uploaded" type="button" value="download-coa"><i class="fas fa-upload"></i> COA</button>
			</div>
		<?php
		}
		?>
	</form>
</div>
</div>

<?= $this->block('product-summary.php') ?>

<hr>

<form action="/result/create/save" method="post">
<div>

	<?= $this->block('result-metric-input.php') ?>

	<div class="form-actions">

		<div class="form-group">
			<div class="input-group">
				<div class="input-group-text">Status:</div>
				<select id="test-status" class="form-control r section_status" name="test_status" required type="text">
					<option value=""> - - </option>
					<option value="not_started">Not Started</option>
					<option value="in_progress">In Progress</option>
					<option value="completed">Completed</option>
				</select>
			</div>
		</div>

		<input name="sample_id" type="hidden" value="<?= $data['Sample']['id'] ?>">
		<button class="btn btn-outline-secondary" name="a" type="submit" value="save"><i class="fas fa-save"></i> Save</button>
		<button class="btn btn-outline-primary" name="a" type="submit" value="commit"><i class="fas fa-upload"></i> Commit</button>
	</div>

</div>
</form>


<?= $this->block('modal-coa-upload.php') ?>

<script>
$(document).ready(function(){
	<?php
	include_once(__DIR__ . '/create.js');
	?>
});
</script>
