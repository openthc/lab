<?php
/**
 * Incoming Data Portal
 * Turn this into two steps
 *  1) Origin Information + Data File
 *  2a) if Data File confirm Sample Data
 *  2b) No Data File input/create Sample Data
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

$variety_package_list = [];
$variety_package_list[] = [
	'id' => '1',
	'name' => 'Test'
];

?>

<div class="hero-wrap">
	<div class="hero-core">
		<h1>Sample Intake</h1>
		<?php
		// Laboratyr Data Here
		// echo $data['Laboratory']['intake-note'];
		?>
	</div>
</div>


<form autocomplete="off" method="post">
<div class="container mt-4" style="min-height: 80vh;">

<div class="mb-2">
	<div class="input-group">
		<div class="input-group-text">Laboratory:</div>
		<input autofocus name="laboratory_license" class="form-control license-autocomplete" value="<?= __h($data['Laboratory_License']['name']) ?>">
		<input class="autocomplete-data-id" id="laboratory-license-id" name="laboratory-license_id" type="hidden" value="">
		<button class="btn btn-outline-secondary btn-autocomplete-hint" type="button"><i class="fas fa-sync"></i></button>
		<a class="btn btn-outline-secondary" href="#" id="license-id-link" target="_blank"><i class="fas fa-address-book"></i></a>
	</div>
</div>

<div class="row">
	<div class="col-md-6">
		<div class="mb-2">
			<div class="input-group">
				<div class="input-group-text">Origin License:</div>
				<input autofocus name="origin_license" class="form-control license-autocomplete">
				<input class="autocomplete-data-id" id="license-id" name="license_id" type="hidden" value="">
				<button class="btn btn-outline-secondary btn-autocomplete-hint" type="button"><i class="fas fa-sync"></i></button>
				<a class="btn btn-outline-secondary" href="#" id="license-id-link" target="_blank"><i class="fas fa-address-book"></i></a>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="mb-2">
			<div class="input-group">
				<div class="input-group-text">Origin Contact:</div>
				<input autofocus name="origin_contact" class="form-control">
				<button class="btn btn-outline-secondary btn-autocomplete-hint" type="button"><i class="fas fa-sync"></i></button>
			</div>
		</div>
	</div>
</div>

<hr>

<h2>Sample Data</h2>
<p>Enter the Sample information here,
<table class="table">
<thead class="table-dark">
	<tr>
		<td>Inventory ID</td>
		<td>Product Type</td>
		<td>Product</td>
		<td>Variety</td>
		<td>Origin Quantity</td>
		<td>Sample Quantity</td>
		<td></td>
	</tr>
</thead>
<tbody>
<?php
foreach ($variety_package_list as $i => $p) {
?>
	<tr>
		<td>
			<input class="form-control" name="inventory-id[]" placeholder="Inventory Lot Serial Number">
		</td>
		<td>
			<!-- @todo Data List -->
			<!-- <input class="form-control" name="product_type[]" placeholder="- product type -" value="<?= h($p['product_type']) ?>"> -->
			<select class="form-control" name="product_type[]">
				<option value="">- Select Product Type -</option>
				<?php
				foreach ($data['product_type'] as $pi => $pt) {
					printf('<option value="%s">%s</option>', $pi, $pt);
				}
				?>
			</select>
		</td>
		<td>
			<input class="form-control" name="product_name[]" placeholder="- eg: Bulk Flower -" value="<?= h($p['product_name']) ?>">
		</td>
		<td>
			<input class="form-control" name="variety_name[]" placeholder="- Alpha Dawg -" type="text" value="<?= (intval($p['variety_name']) ?: '') ?>">
		</td>
		<!-- Origin -->
		<td>
			<div class="input-group">
				<input class="form-control r" name="origin-qty[]" min="0" placeholder="- 5.00 -" step="0.01" type="number" value="<?= (floatval($p['qty']) ?: '') ?>">
				<select class="form-select" name="origin-uom[]" style="max-width: 6em;">
					<option value="ea">ea</option>
					<option value="g">g</option>
					<!-- <option value="lb">lb</option> -->
				</select>
			</div>
		</td>
		<!-- Sample -->
		<td>
			<div class="input-group">
				<input class="form-control r" name="sample-qty[]" min="0" placeholder="- 5.00 -" step="0.01" type="number" value="<?= (floatval($p['qty']) ?: '') ?>">
				<select class="form-select" name="sample-uom[]" style="max-width: 6em;">
					<option value="ea">ea</option>
					<option value="g">g</option>
					<!-- <option value="lb">lb</option> -->
				</select>
			</div>
		</td>
		<td class="r">
			<div class="btn-group">
				<button class="btn btn-primary btn-package-add" type="button"><i class="fas fa-plus-square"></i></button>
				<button class="btn btn-outline-danger btn-package-del" type="button"><i class="fas fa-trash"></i></button>
			</div>
		</td>
	</tr>
<?php
}
?>
</tbody>
</table>

<div>
	<h2>File Attachments / Links</h2>
	<p>Add any file attachments, or data links here</p>

	<!-- <div>Drop Zone?</div> -->
	<div class="input-group">
		<div class="input-group-text">File:</div>
		<input class="form-control" name="data-file"  type="file">
	</div>

	<div class="input-group">
		<div class="input-group-text">Link:</div>
		<input class="form-control" name="data-link" type="url">
	</div>

</div>


<div class="form-actions mt-4">
	<button class="btn btn-primary" name="a" type="submit" value="lab-intake-save"><i class="fas fa-save"></i> Save / Upload</button>
</div>

</div>
</form>

<script>
$(function() {
	// Package Options for Each/Pack/Retail
	$(document).on('click', '.btn-package-add', function() {
		var tb = $(this).closest('tbody');
		var tr0 = $(this).closest('tr');
		var tr1 = $(tr0).clone();
		tb.append(tr1);
		tr1.find(':input').first().focus();
	});
	$(document).on('click', '.btn-package-del', function() {
		var tb = $(this).closest('tbody');
		var len = tb.find('tr').length;
		if (len > 1) {
			var $p = $(this).closest('tr');
			$p.remove();
		}
	});
});
</script>
