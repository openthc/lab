<div class="row">
	<div class="col-md-4">
		<div class="form-group">
			<label>Product</label>
			<input class="form-control" readonly value="<?= __h($data['Product']['name']) ?>">
		</div>
	</div>
	<div class="col-md-4">
		<div class="form-group">
			<label>Variety</label>
			<input class="form-control" readonly value="<?= __h($data['Variety']['name']) ?>">
		</div>
	</div>
	<div class="col">
		<div class="form-group">
			<label>Sample Type</label>
			<input class="form-control" readonly value="<?= __h($data['Product_Type']['name']) ?>">
		</div>
	</div>
</div>
