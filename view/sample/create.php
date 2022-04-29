<?php
/**
 * View to Single Sample
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

if (empty($data['Lab_Sample']['name'])) {

	$dt0 = new DateTime();
	// $dt0->setTimezone(new DateTimezone($_SESSION['tz']));
	$data['Lab_Sample']['name'] = $dt0->format('Yz-Hm');
}

?>

<h1>Sample :: Create</h1>

<form autocomplete="off" method="post">
<div class="container mt-4" style="min-height: 80vh;">

	<div class="mb-2">
		<div class="input-group">
			<div class="input-group-text">Sample ID:</div>
			<input autofocus name="sample-name" class="form-control" value="<?= $data['Lab_Sample']['name'] ?>">
			<button class="btn btn-outline-secondary" id="lab-sample-id-create" type="button"><i class="fas fa-magic"></i></button>
		</div>
	</div>

	<div class="mb-2">
		<div class="input-group">
			<div class="input-group-text">Origin Lot:</div>
			<input autofocus name="inventory_guid" class="form-control license-autocomplete" value="<?= __h($data['Source_Lot']['guid']) ?>">
			<input id="inventory-id" name="inventory-id" type="hidden" value="<?= __h($data['Source_Lot']['id']) ?>">
			<button class="btn btn-outline-secondary btn-autocomplete-hint" type="button"><i class="fas fa-sync"></i></button>
		</div>
	</div>

	<div class="mb-2">
		<div class="input-group">
			<div class="input-group-text">Origin License:</div>
			<input autofocus name="license_origin" class="form-control license-autocomplete" value="<?= __h($data['Source_License']['name']) ?>">
			<input class="autocomplete-data-id" id="license-id" name="license-id" type="hidden" value="<?= __h($data['Source_License']['id']) ?>">
			<button class="btn btn-outline-secondary btn-autocomplete-hint" type="button"><i class="fas fa-sync"></i></button>
		</div>
	</div>

	<div class="mb-2">
		<div class="input-group">
			<div class="input-group-text">Origin Identifier:</div>
			<input name="lot-id-source" class="form-control" value="<?= __h($data['Source_Lot']['guid']) ?>">
		</div>
	</div>

	<div class="mb-2">
		<div class="input-group">
			<div class="input-group-text">Product Type:</div>
			<select class="form-control" name="product-type">
				<option value="">- Select Product Type -</option>
				<?php
				foreach ($data['product_type'] as $pi => $pt) {
					$sel = ($pi == $data['Source_Product']['product_type_id'] ? 'selected' : '');
					printf('<option %s value="%s">%s</option>', $sel, $pi, $pt);
				}
				?>
			</select>
		</div>
	</div>

	<div class="mb-2">
		<div class="input-group">
			<div class="input-group-text">Product Name:</div>
			<input name="product" class="form-control product-autocomplete" value="<?= __h($data['Source_Product']['name']) ?>">
			<input id="product-id" name="product-id" type="hidden" value="<?= __h($data['Source_Product']['id']) ?>">
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
			<input name="variety-name" class="form-control variety-autocomplete" value="<?= __h($data['Source_Variety']['name']) ?>">
			<input id="variety-id" name="variety-id" type="hidden" value="<?= __h($data['Source_Variety']['id']) ?>">
			<button class="btn btn-outline-secondary btn-autocomplete-hint" type="button"><i class="fas fa-sync"></i></button>
		</div>
	</div>

	<div class="mb-2">
		<div class="input-group">
			<div class="input-group-text">QTY:</div>
			<input name="qty" class="form-control r" type="number" step="0.0001" value="<?= sprintf('%0.2f', $data['Source_Lot']['qty']) ?>">
			<select class="form-control">
				<option value="ea">ea</option>
				<option value="g">g</option>
				<option value="mg">mg</option>
				<option value="ml">ml</option>
			</select>
		</div>
	</div>

	<div class="form-actions">
		<button class="btn btn-primary" name="a" value="create-sample"><i class="fas fa-save"></i> Save</button>
	</div>

</div>
</form>


<script>
$(function() {
	$('#lab-sample-id-create').on('click', function() {
		// AJAX to Generate a Sample ID?
	});
});
</script>
