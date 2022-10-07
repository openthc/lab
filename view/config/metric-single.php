<?php
/**
 * Edit a Single Metric
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

?>

<form method="post">
<div class="container mt-2">

<h1><a href="/config">Config</a> :: <a href="/config/metric">Metric</a> :: Update</h1>

<div class="row">
	<div class="col-md-4 mb-2">
		<div class="input-group">
			<div class="input-group-text">Name:</div>
			<input class="form-control" name="lod" value="<?= __h($data['Lab_Metric']['name']) ?>">
		</div>
	</div>
	<div class="col-md-8 mb-2">
		<div class="input-group">
			<div class="input-group-text">Note:</div>
			<input class="form-control" name="lod" value="<?= __h($data['Lab_Metric']['note']) ?>">
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-4 mb-2">
		<div class="input-group">
			<div class="input-group-text">UOM:</div>
			<input class="form-control r" name="uom" value="<?= __h($data['Lab_Metric']['meta']['uom']) ?>">
		</div>
	</div>
	<div class="col-md-4 mb-2">
		<div class="input-group">
			<div class="input-group-text">LOD:</div>
			<input class="form-control r" name="lod" value="<?= __h($data['Lab_Metric']['meta']['lod']) ?>">
		</div>
	</div>
	<div class="col-md-4 mb-2">
		<div class="input-group">
			<div class="input-group-text">LOQ-LB:</div>
			<input class="form-control r" name="loq-lb" value="<?= __h($data['Lab_Metric']['meta']['loq-lb']) ?>">
		</div>
	</div>
	<div class="col-md-4">
		<div class="input-group">
			<div class="input-group-text">LOQ-UB:</div>
			<input class="form-control r" name="loq-ub" value="<?= __h($data['Lab_Metric']['meta']['loq-ub']) ?>">
		</div>
	</div>
	<div class="col-md-4">
		<div class="input-group">
			<div class="input-group-text">Action Limit:</div>
			<input class="form-control r" name="lof" value="<?= __h($data['Lab_Metric']['meta']['max']['val']) ?>">
		</div>
	</div>
</div>

<?php
if (false) {
?>

<hr>

<div class="row mt-2">
	<div class="col-md-12">

		<h2>Product Types</h2>
		<p>Enable / disable this Metric for specific Product Types</p>

		<div class="result-metric-wrap">
		<?php
		foreach ($data['Product_Type_list'] as $pt_ulid => $pt_name) {
			echo '<div class="result-metric-data">';

				echo '<div class="input-group">';
					echo '<div class="input-group-text">';
					printf('<input name="product-type-%s" type="checkbox" value="1">', $pt_ulid);
					echo '</div>';
					printf('<input class="form-control" readonly value="%s">', __h($pt_name));
				echo '</div>';
			echo '</div>';
		}
		?>
		</div>

	</div>
</div>
<?php
}
?>

<div class="form-actions">
	<button class="btn btn-primary" name="a" type="submit" value="lab-metric-single-update"><i class="fa-solid fa-save"></i> Save</button>
</div>

</div>
</form>
