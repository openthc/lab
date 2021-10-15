<?php
/**
 *
 */

use \App\Lab_Metric;

?>

<style>
.table-metric label {
	display: block;
	margin: 0;
}
.table-metric td {
	font-size: 120%;
}
.table-metric td label {
	user-select: none;
}
</style>

<div class="container mt-2">

<h1>Config :: Metrics</h1>
<p>Configure which metrics are used with which product classes.</p>

<form method="post">
<table class="table table-sm table-bordered table-metric">
<?php
foreach ($this->data['metric_list'] as $m) {

	if ($m['type'] != $type_x) {
	?>
		<tr class="thead-dark">
			<th colspan="7"><?= h($m['type']) ?></th>
		</tr>
		<tr class="thead-dark">
			<th>Name</th>
			<th>LOD</th>
			<th>LOQ</th>
			<th>Max</th>
			<th colspan="3">Products</th>
		</tr>
	<?php
	}

?>
	<tr>
		<td><?= h($m['name']) ?></td>
		<td class="r">
			<div class="input-group input-group-sm" style="width: 8em;">
				<input class="form-control form-control-sm r" name="<?= sprintf('%s-lod', $m['id']) ?>" value="<?= h($m['meta']['lod']) ?>">
				<div class="input-group-append">
					<div class="input-group-text">ppm</div>
				</div>
			</div>
		</td>
		<td class="r">
			<div class="input-group input-group-sm" style="width: 8em;">
				<input class="form-control form-control-sm r" name="<?= sprintf('%s-loq', $m['id']) ?>" value="<?= h($m['meta']['loq']) ?>">
				<div class="input-group-append">
					<div class="input-group-text">ppm</div>
				</div>
			</div>
		</td>
		<td class="r">
			<div class="input-group input-group-sm" style="width: 8em;">
				<input class="form-control form-control-sm r" name="<?= sprintf('%s-max', $m['id']) ?>" value="<?= h($m['meta']['max']) ?>">
				<div class="input-group-append">
					<div class="input-group-text">ppm</div>
				</div>
			</div>
		</td>
		<td><label><input <?= ($m['flag'] & Lab_Metric::FLAG_FLOWER) ? 'checked' : null ?> name="<?= sprintf('%s-bud', $m['id']) ?>" type="checkbox"> Flower</label></td>
		<td><label><input <?= ($m['flag'] & Lab_Metric::FLAG_EDIBLE) ? 'checked' : null ?> name="<?= sprintf('%s-edi', $m['id']) ?>" type="checkbox"> Edible</label></td>
		<td><label><input <?= ($m['flag'] & Lab_Metric::FLAG_EXTRACT) ? 'checked' : null ?> name="<?= sprintf('%s-ext', $m['id']) ?>"  type="checkbox"> Extract</label></td>
	</tr>

	<?php
	$type_x = $m['type'];
	?>

<?php
}
?>
</table>

<div class="form-actions">
	<button class="btn btn-outline-primary" name="a" value="save"><i class="fas fa-save"></i> Save</button>
</div>
</form>

</div>

<script>
function randomVal()
{
	$('input').val('5.62');
	$('input[type=checkbox]').prop('checked', true);
}
</script>
