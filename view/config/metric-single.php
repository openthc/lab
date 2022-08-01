<?php
/**
 * Edit a Single Metric
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

?>

<form method="post">
<div class="container mt-2">

<h1><a href="/config">Config</a> :: Metrics :: Update</h1>

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
			<input class="form-control" name="lod" value="<?= __h($data['Lab_Metric']['name']) ?>">
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-4 mb-2">
		<div class="input-group">
			<div class="input-group-text">UOM:</div>
			<input class="form-control r" name="uom" value="<?= __h($data['Lab_Metric']['uom']) ?>">
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
			<input class="form-control r" name="loq-lb" value="<?= h($m['meta']['loq-lb']) ?>">
		</div>
	</div>
	<div class="col-md-4">
		<div class="input-group">
			<div class="input-group-text">LOQ-UB:</div>
			<input class="form-control r" name="loq-ub" value="<?= h($m['meta']['loq-ub']) ?>">
		</div>
	</div>
	<div class="col-md-4">
		<div class="input-group">
			<div class="input-group-text">Action Limit:</div>
			<input class="form-control r" name="lof" value="<?= h($m['meta']['max']['val']) ?>">
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">

		<h2>Product Types</h2>
		<p>Enable / disable this Metric for specific Product Types</p>

		<div class="d-flex">
		<?php
		foreach ($data['product_type_list'] as $pt) {
			echo '<div>';
			echo __h($pt['name']);
			echo '<input type="checkbox">';
			echo '</div>';
		}
		?>
		</div>

	</div>
</div>

</div>
</form>
