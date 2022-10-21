<?php
/**
 * Configure the COA Details
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

?>

<div class="container mt-2">

<h1><a href="/config">Config</a> :: COA</h1>
<p>Configure values for the COA Outputs.</p>

<form autocomplete="off" enctype="multipart/form-data" method="post">
<div class="input-group mt-2">
	<div class="input-group-prepend">
		<div class="input-group-text">Display Name:</div>
	</div>
	<input class="form-control" name="config-coa-company-name" value="<?= __h($data['config-coa-company-name']) ?>">
</div>

<div class="input-group mt-2">
	<div class="input-group-prepend">
		<div class="input-group-text">Address Line 1:</div>
	</div>
	<input class="form-control" name="coa/address/line/1" value="<?= __h($data['coa/address/line/1']) ?>">
</div>

<div class="input-group mt-2">
	<div class="input-group-prepend">
		<div class="input-group-text">Address Line 2:</div>
	</div>
	<input class="form-control" name="coa/address/line/2" value="<?= __h($data['coa/address/line/2']) ?>">
</div>

<div class="input-group mt-2">
	<div class="input-group-prepend">
		<div class="input-group-text">Phone:</div>
	</div>
	<input class="form-control" name="coa/phone" value="<?= __h($data['coa/phone']) ?>">
</div>

<div class="input-group mt-2">
	<div class="input-group-prepend">
		<div class="input-group-text">Email:</div>
	</div>
	<input class="form-control" name="coa/email" value="<?= __h($data['coa/email']) ?>">
</div>

<div class="input-group mt-2">
	<div class="input-group-prepend">
		<div class="input-group-text">Website:</div>
	</div>
	<input class="form-control" name="coa/website" value="<?= __h($data['coa/website']) ?>">
</div>

<div class="input-group mt-2">
	<div class="input-group-prepend">
		<div class="input-group-text">Footer Text:</div>
	</div>
	<textarea class="form-control" name="coa/footer"><?= __h($data['coa/footer']) ?></textarea>
</div>

<div class="input-group mt-2">
	<div class="input-group-prepend">
		<div class="input-group-text">Logo/Icon:</div>
	</div>
	<input class="form-control" name="coa/icon" type="file">
	<?php
	if ( ! empty($data['coa/icon'])) {
		if (is_file($data['coa/icon'])) {
			$img = file_get_contents($data['coa/icon']);
			$img_b64 = base64_encode($img);
			echo '<div class="input-group-append">';
			printf('<img alt="Company Icon" src="data:text/plain;base64,%s">', $img_b64);
			// echo $data['coa/icon'];
			echo '</div>';
		}
	}
	?>
</div>


<div class="form-actions mt-4">
	<button class="btn btn-primary" name="a" type="submit" value="config-coa-save"><i class="fas fa-save"></i></button>
</div>
</form>

</div>
