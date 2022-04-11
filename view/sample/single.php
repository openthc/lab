<?php
/**
 * View Lab Sample
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

?>

<form method="post">
<div class="container">

<h1>
Sample :: <?= $data['Lab_Sample']['id_nice'] ?>
<?= $data['Lab_Sample']['flag_medical'] ? '<i class="fas fa-medkit"></i>' : null ?>
</h1>

<div class="row">
	<div class="col-md-6">
		<div class="mb-2">
			<label>Sample:</label>
			<div class="form-group input-group">
				<input class="form-control" name="lab-sample-name" value="<?= __h($data['Lab_Sample']['name']) ?>">
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="mb-2">
			<label>Source License:</label>
			<div class="input-group">
				<input name="license-name-source" class="form-control license-autocomplete" value="<?= __h($data['License_Source']['name']) ?>">
				<input class="autocomplete-data-id" id="license-id-source" name="license-id-source" type="hidden" value="<?= $data['License_Source']['id'] ?>">
				<button class="btn btn-outline-secondary btn-autocomplete-hint" type="button"><i class="fas fa-sync"></i></button>
				<a class="btn btn-outline-secondary" href="/reports/b2b/license-detail??id=<?= $data['License_Source']['id'] ?>"><i class="fas fa-link"></i></a>
			</div>
		</div>
	</div>
</div>


<div class="row">
	<div class="col-md-6">
		<div class="mb-2">
			<label>Source Lot Identifier:</label>
			<input class="form-control" name="lot-id-source" value="<?= $data['Lot']['guid'] ?>">
		</div>
	</div>
	<div class="col-md-6">
		<div class="mb-2">
			<label>Product Type</label>
			<div class="input-group">
				<input class="form-control" readonly value="<?= __h($data['ProductType']['name']) ?>">
				<!-- <div class="input-group-append">
					<button class="btn btn-outline-secondary btn-field-edit" type="button"><i class="fas fa-edit"></i></button>
				</div> -->
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-6">
		<div class="mb-2">
			<label>Product</label>
			<div class="input-group">
				<input class="form-control product-autocomplete" name="product-name" value="<?= __h($data['Product']['name']) ?>">
				<input id="product-id" name="product-id" type="hidden" value="<?= $data['Product']['id'] ?>">
				<div class="input-group-text"><?= ($data['Lab_Sample']['flag_medical'] ? 'Med' : 'Rec') ?></div>
				<button class="btn btn-outline-secondary btn-autocomplete-hint" type="button"><i class="fas fa-sync"></i></button>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="mb-2">
			<label>Variety / Strain:</label>
			<div class="input-group">
				<input class="form-control variety-autocomplete" name="variety-name" value="<?= __h($data['Variety']['name']) ?>">
				<input class="" name="variety-id" type="hidden" value="<?= $data['Variety']['id'] ?>">
				<button class="btn btn-outline-secondary btn-autocomplete-hint" type="button"><i class="fas fa-sync"></i></button>
			</div>
		</div>
	</div>
</div>


<!-- <p>Source ID: <code>{{ Lab_Sample.global_original_id }}</code>
	if Lab_Sample.meta.external_id or Lab_Sample.meta.legacy_id {
		{{ Lab_Sample.meta.external_id ? ("External ID: <code>" ~ Lab_Sample.meta.external_id ~ "</code>")|raw }}
		{{ Lab_Sample.meta.legacy_id ? ("Legacy ID: <code>" ~ Lab_Sample.meta.legacy_id ~ "</code>")|raw }}
	}
	from <strong>{{ License_Source.name }}</strong> [{{ License_Source.code }}]
</p> -->

<div class="row">
	<div class="col-md-6">
		<div class="mb-2">
			<label>Quantity</label>
			<div class="input-group">
				<input class="form-control r" name="sample-qty" min="1" step="0.01" type="number" value="<?= __h($data['Lab_Sample']['qty']) ?>">
				<div class="input-group-text"><?= __h($data['Product']['uom']) ?></div>
			</div>
		</div>
	</div>
</div>


<?php
if ( ! empty($data['Lab_Result_list'])) {
?>

	<hr>
	<h2>Lab Results</h2>
	<?php
	foreach ($data['Lab_Result_list'] as $lr) {
		printf('<p>Lab Result: <a href="/result/%s">%s</a></p>', $lr['id'], __h($lr['name'] ?: $lr['guid'] ?: $lr['id']) );
	}

}
?>

<div class="form-actions">
	<?php
	switch ($data['Lab_Sample']['stat']) {
		case 100:
			echo '<button class="btn btn-outline-primary" name="a" type="submit" value="accept-sample"><i class="fas fa-sync"></i> Accept</button>';
			break;
		case 200:
			printf('<a class="btn btn-outline-primary" href="/result/create?sample_id=%s"><i class="fas fa-plus"></i> Add Results</a>', $data['Lab_Sample']['id']);
			break;
	}
	?>
	<button class="btn btn-outline-secondary" name="a" type="submit" value="save"><i class="fas fa-save"></i> Save</button>
	<!-- <a class="btn btn-outline-secondary" href="/sample/<?= $data['Lab_Sample']['id'] ?>/edit"><i class="fas fa-edit"></i> Edit</a> -->
	<!-- <button class="btn btn-outline-secondary" name="a" type="submit" value="done"><i class="fas fa-check-square"></i> Finish</button> -->
	<button class="btn btn-outline-danger" name="a" type="submit" value="void"><i class="fas fa-ban"></i> Void</button>
	<button class="btn btn-outline-danger" name="a" type="submit" value="drop"><i class="fas fa-trash"></i> Delete</button>
</div>

</div>
</form>
