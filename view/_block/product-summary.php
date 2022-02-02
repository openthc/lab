<?php
/**
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

?>

<div class="row">
	<div class="col-md-4">
		<div class="mb-2">
			<label>Product</label>
			<input class="form-control" readonly value="<?= __h($data['Product']['name']) ?>">
		</div>
	</div>
	<div class="col-md-4">
		<div class="mb-2">
			<label>Variety</label>
			<input class="form-control" readonly value="<?= __h($data['Variety']['name']) ?>">
		</div>
	</div>
	<div class="col">
		<div class="mb-2">
			<label>Sample Type</label>
			<input class="form-control" readonly value="<?= __h($data['Product_Type']['name']) ?>">
		</div>
	</div>
</div>
