
<style>
/*
	SSO and Authentication Stuff
*/
.page-wrap {
	margin: 10vh auto 0 auto;
	max-width: 690px;
}

.page-wrap .card {
	min-height: 420px;
}
.page-wrap .card h2 {
	margin-bottom: 0.25rem;
}
.page-wrap .card h3 {
	margin-bottom: 0.25rem;
}

@media (max-width: 768px) {

	.page-wrap {
		margin: 0 auto;
		max-width: 100%;
	}

}
</style>


<div class="page-wrap">
<div class="card">
<h1 class="card-header">Lab Result Not Found</h1>
<div class="card-body">

	<h2>This result may not be uploaded yet</h2>
	<p class="card-text">We may be waiting on one or more parties to take action to provide these results.</p>

	<hr>

	<h3>Suppliers:</h3>
	<p>Suppliers (Growers &amp; Processors) can connect their inventory systems to automatically import data.</p>
	<p>Simply <a href="/auth/open">sign-in</a> to get started</p>

	<hr>

	<h3>Laboratories:</h3>
	<p>
		Your lab may have a one-time code from a supplier to use for uploading results.
		If not, you may reach out to the client who owns these results for access.
	</p>
	<?php
	if ($data['lab_result_id']) {
		printf('<p>Or, you may to <a href="/auth/open?want=%s">request access</a>.</p>', $data['lab_result_id']);
	}
	?>

</div>
</div>
</div>
