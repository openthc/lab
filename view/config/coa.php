<?php
/**
 * Configure the COA Details
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

?>

<div class="input-group">
	<div class="input-group-prepend">
		<div class="input-group-text" style="width: 6em;">Display Name:</div>
	</div>
	<input class="form-control" name="config-coa-company-name" value="<?= __h($data['config-coa-company-name']) ?>">
</div>

<div class="input-group">
	<div class="input-group-prepend">
		<div class="input-group-text" style="width: 6em;">Address Line 1:</div>
	</div>
	<input class="form-control" name="config-coa-address-1" value="<?= __h($data['config-coa-address-1']) ?>">
</div>

<div class="input-group">
	<div class="input-group-prepend">
		<div class="input-group-text" style="width: 6em;">Address Line 2:</div>
	</div>
	<input class="form-control" name="config-coa-address-2" value="<?= __h($data['config-coa-address-2']) ?>">
</div>

<div class="input-group">
	<div class="input-group-prepend">
		<div class="input-group-text" style="width: 6em;">Phone:</div>
	</div>
	<input class="form-control" name="config-coa-phone" value="<?= __h($data['config-coa-phone']) ?>">
</div>

<div class="input-group">
	<div class="input-group-prepend">
		<div class="input-group-text" style="width: 6em;">Email:</div>
	</div>
	<input class="form-control" name="config-coa-email" value="<?= __h($data['config-coa-email']) ?>">
</div>

<div class="input-group">
	<div class="input-group-prepend">
		<div class="input-group-text" style="width: 6em;">Website:</div>
	</div>
	<input class="form-control" name="config-coa-website" value="<?= __h($data['config-coa-website']) ?>">
</div>


<div class="form-actions">
	<button class="btn btn-primary" name="a" type="submit" value="config-coa-save"><i class="fas fa-save"></i></button>
</div>
