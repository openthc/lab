
<div class="container mt-2">

<h1><a href="/config">Config</a> ::  Intake Portal</h1>

<h2>Web Portal Link</h2>

<div>
	<div class="input-group">
		<input class="form-control form-control-lg" readonly type="text" value="<?= $data['intake_link'] ?>">
		<button
			class="btn btn-outline-secondary btn-clipcopy"
			data-clipboard-text="<?= $data['intake_link'] ?>"
			type="button">
				<i class="fas fa-clipboard"></i>
		</button>
	</div>
	<p>It's recommended that you create a redirect from your own site to this link.</p>
</div>

<div class="mt-2">
	<h3>Require Client ID</h3>
	<p>Requires a client put a semi-secret value into the intake form.</p>
</div>

<div class="mt-2">
	<h3>Require Client Sign In</h3>
	<p>Client will be required to create an account and sign-in to use the Intake Portal.</p>
</div>


<div class="mt-2">
	<h3>Require Special Link?</h3>
	<p>The portal link must be generated for every client order</p>
</div>

</div>
