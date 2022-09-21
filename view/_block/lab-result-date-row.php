<?php
/**
 * Show Report/Result Created, Approved and Expires
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

$tz = $_SESSION['tz'];
if (empty($tz)) {
	$tz = 'UTC';
}

$dtC = new \DateTime($data['Lab_Report']['created_at']);
$dtC->setTimezone(new \DateTimezone($tz));

$dtC_hint = '';
$dtC_html = $dtC->format('Y-m-d H:i T');

// Approved
$dtA_hint = $dtA_html = 'No Approval Set';
if ( ! empty($data['Lab_Report']['approved_at'])) {
	$dtA = new \DateTime($data['Lab_Report']['approved_at']);
	$dtA->setTimezone(new \DateTimezone($tz));
	$dtA_html = $dtA->format('Y-m-d H:i T');
}

// Expires
$dtE_hint = $dtE_html = 'No Expiration Set';
if ( ! empty($data['Lab_Report']['expires_at'])) {
	$dtE = new \DateTime($data['Lab_Report']['expires_at']);
	$dtE->setTimezone(new \DateTimezone($tz));
	$dtE_html = $dtE->format('Y-m-d H:i T');
}

?>

<div class="row">
	<div class="col-md-4">
		<div class="input-group">
			<div class="input-group-text">Created</div>
			<div class="form-control"><?= $dtC_html ?></div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="input-group">
			<div class="input-group-text">Approved</div>
			<div class="form-control"><?= $dtA_html ?></div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="input-group">
			<div class="input-group-text">Expires</div>
			<div class="form-control"><?= $dtE_html ?></div>
		</div>
	</div>
</div>
