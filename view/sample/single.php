<?php
/**
 * View Lab Sample
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

use OpenTHC\Lab\Lab_Sample;
use OpenTHC\Lab\Lab_Report;
use OpenTHC\Lab\Lab_Result;

?>

<form autocomplete="off" enctype="multipart/form-data" method="post">
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
			<label>Image:</label>
			<div class="input-group">
				<input accept=".png,.jpeg,.jpg,image/png,image/jpeg" capture="environment" class="form-control" name="sample-file" type="file">
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-6">
		<div class="mb-2">
			<label>Source Lot Identifier:</label>
			<input class="form-control" name="source-lot-guid" value="<?= $data['Lot']['guid'] ?>">
		</div>
	</div>
	<div class="col-md-6">
		<div class="mb-2">
			<label>Source License:</label>
			<div class="input-group">
				<input name="source-license-name" class="form-control license-autocomplete" value="<?= __h($data['License_Source']['name']) ?>">
				<input class="autocomplete-data-id" id="source-license-id" name="source-license-id" type="hidden" value="<?= $data['License_Source']['id'] ?>">
				<button class="btn btn-outline-secondary btn-autocomplete-hint disabled" type="button"><i class="fas fa-sync"></i></button>
				<!-- <a class="btn btn-outline-secondary" href="/reports/b2b/license-detail??id=<?= $data['License_Source']['id'] ?>"><i class="fas fa-link"></i></a> -->
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-6">
		<div class="mb-2">
			<label>Product Type:</label>
			<div class="input-group">
				<input class="form-control" readonly value="<?= __h($data['ProductType']['name']) ?>">
				<!-- <div class="input-group-append">
					<button class="btn btn-outline-secondary btn-field-edit" type="button"><i class="fas fa-edit"></i></button>
				</div> -->
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="mb-2">
			<label>Product:</label>
			<div class="input-group">
				<input class="form-control product-autocomplete" name="product-name" value="<?= __h($data['Product']['name']) ?>">
				<input id="product-id" name="product-id" type="hidden" value="<?= $data['Product']['id'] ?>">
				<div class="input-group-text"><?= ($data['Lab_Sample']['flag_medical'] ? 'Med' : 'Rec') ?></div>
				<button class="btn btn-outline-secondary btn-autocomplete-hint" type="button"><i class="fas fa-sync"></i></button>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-6">
		<div class="mb-2">
			<label>Variety:</label>
			<div class="input-group">
				<input class="form-control variety-autocomplete" name="variety-name" value="<?= __h($data['Variety']['name']) ?>">
				<input class="" name="variety-id" type="hidden" value="<?= $data['Variety']['id'] ?>">
				<button class="btn btn-outline-secondary btn-autocomplete-hint" type="button"><i class="fas fa-sync"></i></button>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="mb-2">
			<label>Quantity:</label>
			<div class="input-group">
				<input class="form-control r" name="sample-qty" min="0" step="0.01" type="number" value="<?= __h($data['Lab_Sample']['qty']) ?>">
				<div class="input-group-text"><?= __h($data['Product']['uom']) ?></div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col">
		<div class="mb-2">
			<label>Notes:</label>
			<textarea class="form-control" name="lab-sample-note"><?= __h($data['Lab_Sample']['note']) ?></textarea>
		</div>
	</div>
</div>

<?php
if ( ! empty($data['Lab_Sample']['img_link'])) {
	echo '<section>';
	echo '<h2>Sample Media</h2>';
	echo '<div class="img-fluid">';
	printf('<img alt="Sample Image" src="%s" style="max-width:360px;">', $data['Lab_Sample']['img_link']);
	echo '</div>';
	echo '</section>';
}
?>



<div class="form-actions">
	<?php
	switch ($data['Lab_Sample']['stat']) {
		case Lab_Sample::STAT_OPEN:
			echo '<button class="btn btn-primary" name="a" type="submit" value="accept-sample"><i class="fas fa-sync"></i> Accept</button>';
			break;
		case Lab_Sample::STAT_LIVE:
			printf('<a class="btn btn-primary" href="/result/create?sample_id=%s"><i class="fas fa-plus"></i> Add Results</a>', $data['Lab_Sample']['id']);
			break;
	}
	?>
	<button class="btn btn-primary" name="a" type="submit" value="save"><i class="fas fa-save"></i> Save</button>
	<!-- <a class="btn btn-secondary" href="/sample/<?= $data['Lab_Sample']['id'] ?>/edit"><i class="fas fa-edit"></i> Edit</a> -->
	<!-- <button class="btn btn-secondary" name="a" type="submit" value="done"><i class="fas fa-check-square"></i> Finish</button> -->
	<button class="btn btn-outline-danger" name="a" type="submit" value="void"><i class="fas fa-ban"></i> Void</button>
	<button class="btn btn-outline-danger" name="a" type="submit" value="drop"><i class="fas fa-trash"></i> Delete</button>
</div>
</form>

<?php
if ( ! empty($data['Lab_Result_list'])) {
?>

	<hr>
	<form autocomplete="off" enctype="multipart/form-data" method="post">
	<h2 style="margin-bottom:0;">Lab Results</h2>
	<table class="table table-sm">
	<?php
	foreach ($data['Lab_Result_list'] as $lr) {

		$lr = new Lab_Result(null, $lr);

		$dtC = new \DateTime($lr['created_at'], new \DateTimezone($_SESSION['tz']));

		echo '<tr>';
		printf('<td class="c"><input checked name="lab-result[]" type="checkbox" value="%s"></td>', $lr['id']);
		printf('<td><a href="/result/%s">%s</a></td>', $lr['id'], __h($lr['guid']) );
		printf('<td><a href="/result/%s">%s</a></td>', $lr['id'], __h($lr['name'] ?: $lr['guid'] ?: $lr['id']) );
		printf('<td title="%s">%s</td>', $lr['created_at'], $dtC->format('m/d/y'));

		if (empty($lr['approved_at'])) {
			echo '<td>Not Approved</td>';
		} else {
			$dtA = new \DateTime($lr['approved_at'], new \DateTimezone($_SESSION['tz']));
			printf('<td title="%s">%s</td>', $lr['approved_at'], $dtA->format('m/d/y'));
		}

		if (empty($lr['expires_at'])) {
			echo '<td>n/a</td>';
		} else {
			$dtE = new \DateTime($lr['expires_at'], new \DateTimezone($_SESSION['tz']));
			printf('<td title="%s">%s</td>', $lr['expires_at'], $dtE->format('m/d/y'));
		}

		// printf('<td>%d</td>', $lr['stat']);
		printf('<td>%s</td>', $lr->getStat());
		// printf('<td>%08x</td>', $lr['flag']);

		echo '</tr>';
	}
	?>
	</table>
	<div class="form-actions">
		<button class="btn btn-primary" name="a" type="submit" value="lab-report-create"><i class="fa-solid fa-file-signature"></i> Report</button>
	</div>
	</form>
<?php
}
?>

<?php
if ( ! empty($data['Lab_Report_list'])) {
?>

	<hr>
	<h2 style="margin-bottom:0;">Lab Reports</h2>
	<table class="table table-sm">
	<?php
	foreach ($data['Lab_Report_list'] as $lr) {

		$lr = new Lab_Report(null, $lr);

		$dtC = new \DateTime($lr['created_at']);

		echo '<tr>';
		printf('<td><a href="/report/%s"><code>%s</code></a></td>', $lr['id'], substr($lr['id'], -6) );
		// printf('<td>%s</td>', $dt->format('Y-m-d'));
		printf('<td><a href="/report/%s">%s</a></td>', $lr['id'], __h($lr['name']) );

		printf('<td title="%s">%s</td>', $lr['created_at'], $dtC->format('m/d/y'));

		if (empty($lr['approved_at'])) {
			echo '<td>Not Approved</td>';
		} else {
			$dtA = new \DateTime($lr['approved_at'], new \DateTimezone($_SESSION['tz']));
			printf('<td title="%s">%s</td>', $lr['approved_at'], $dtA->format('m/d/y'));
		}

		if (empty($lr['expires_at'])) {
			echo '<td>n/a</td>';
		} else {
			$dtE = new \DateTime($lr['expires_at'], new \DateTimezone($_SESSION['tz']));
			printf('<td title="%s">%s</td>', $lr['expires_at'], $dtE->format('m/d/y'));
		}

		// printf('<td><a href="/report/%s">%s</a></td>', $lr['id'], __h($lr['name'] ?: $lr['guid'] ?: $lr['id']) );
		// printf('<td class="r"><input name="lab-report[]" type="checkbox" value="%s"></td>', $lr['id']);
		// printf('<td>%d</td>', $lr['stat']);
		printf('<td>%s</td>', $lr->getStat());
		printf('<td>%08x</td>', $lr['flag']);
		printf('<td>%s</td>', $lr->getFlag('s'));


		echo '</tr>';
	}
	echo '</table>';
}
?>


</div>
</form>
