<?php
/**
 * View to Single Sample
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */
?>

<h1>Sample :: Create</h1>

<form method="post">
<div class="container mt-4" style="min-height: 80vh;">


	<div class="mb-2">
		<div class="input-group">
			<div class="input-group-text">Origin License:</div>
			<input autofocus name="license_origin" class="form-control license-autocomplete">
			<input class="autocomplete-data-id" id="license-id" name="license-id" type="hidden" value="">
			<button class="btn btn-outline-secondary btn-autocomplete-hint" type="button"><i class="fas fa-sync"></i></button>
		</div>
	</div>

	<div class="mb-2">
		<div class="input-group">
			<div class="input-group-text">Origin Identifier:</div>
			<input name="lot-id-source" class="form-control">
		</div>
	</div>

	<div class="mb-2">
		<div class="input-group">
			<div class="input-group-text">Product Type:</div>
			<select class="form-control" name="product-type">
				<option value="">- Select Product Type -</option>
				<?php
				foreach ($data['product_type'] as $pi => $pt) {
					printf('<option value="%s">%s</option>', $pi, $pt);
				}
				?>
			</select>
		</div>
	</div>

	<div class="mb-2">
		<div class="input-group">
			<div class="input-group-text">Product Name:</div>
			<input name="product" class="form-control product-autocomplete">
			<input id="product-id" name="product-id" type="hidden" value="">
			<!-- <div class="input-group-append">
				<button class="btn btn-outline-secondary btn-autocomplete-hint" type="button"><i class="fas fa-sync"></i></button>
			</div> -->
		</div>
	</div>

	<div class="mb-2">
		<div class="input-group">
			<div class="input-group-prepend">
				<div class="input-group-text">Variety:</div>
			</div>
			<input name="variety-name" class="form-control variety-autocomplete">
			<input id="variety-id" name="variety-id" type="hidden" value="">
			<button class="btn btn-outline-secondary btn-autocomplete-hint" type="button"><i class="fas fa-sync"></i></button>
		</div>
	</div>

	<div class="mb-2">
		<div class="input-group">
			<div class="input-group-text">QTY:</div>
			<input name="qty" class="form-control r" type="number" step="0.0001">
			<select class="form-control">
				<option value="ea">ea</option>
				<option value="g">g</option>
				<option value="mg">mg</option>
				<option value="ml">ml</option>
			</select>
		</div>
	</div>

	<div>
		<button class="btn btn-primary" name="a" value="create-sample"><i class="fas fa-save"></i> Save</button>
	</div>

</div>
</form>
