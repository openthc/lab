<form autocomplete="off" method="post">
<div class="container">

	<h2>QBench</h2>

	<div class="mb-2">
		<label>QBench API Endpoint</label>
		<input class="form-control" name="qbench-server-url" value="<?= __h($data['qbench-server-url']) ?>">
	</div>

	<div class="mb-2">
		<label>QBench Public Key</label>
		<input class="form-control" name="qbench-public-key" value="<?= __h($data['qbench-public-key']) ?>">
	</div>

	<div class="mb-2">
		<label>QBench Secret Key</label>
		<input class="form-control" name="qbench-secret-key" value="<?= __h($data['qbench-secret-key']) ?>">
	</div>

	<div>
		<button class="btn btn-primary" name="a" type="submit" value="qbench-save"><i class="fas fa-save"></i> Save</button>
	</div>

</div>
</form>
