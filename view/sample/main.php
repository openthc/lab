<?php
/**
 * Show List of Sample Lot Objects
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

?>

<div class="d-flex justify-content-between">
	<div>
		<h1>Samples</h1>
	</div>
	<div class="pt-2">
		<a class="btn btn-primary" href="/sample/create"><i class="fas fa-plus"></i> Create</a>
	</div>
</div>

<form autocomplete="off">
<div class="d-flex mb-2">
	<div>
		<div class="input-group">
			<input autocomplete="off" class="form-control" name="q" placeholder="- search -" value="<?= __h($_GET['q']) ?>">
			<button class="btn btn-outline-secondary"><i class="fas fa-search"></i></button>
		</div>
	</div>
	<div class="ms-2">
		<div class="btn-group">
			<a class="btn btn-outline-secondary" href="?stat=100">Incoming: <?= $data['sample_stat']['100'] ?></a>
			<a class="btn btn-outline-secondary" href="?stat=200">Active: <?= $data['sample_stat']['200'] ?></a>
			<a class="btn btn-outline-success" href="?stat=302">Completed: <?= $data['sample_stat']['302'] ?></a>
			<a class="btn btn-outline-danger" href="?stat=410">Void: <?= $data['sample_stat']['410'] ?></a>
			<a class="btn btn-outline-secondary" href="?stat=*">All</a>
		</div>
	</div>
	<div class="ms-2">
		<div class="btn-group">
			<a class="btn btn-outline-secondary" href="?p=<?= ($data['result_page']['older']) ?>"><i class="fas fa-arrow-left"></i></a>
			<a class="btn btn-outline-secondary" href="?p=<?= ($data['result_page']['newer']) ?>"><i class="fas fa-arrow-right"></i></a>
		</div>
	</div>
<!--
<div class="data-filter-info">
	<button class="btn btn-sm btn-outline-secondary" type="button"><i class="fas fa-filter"></i></button> <em>Active</em>, <em>Untested</em>.
</div>
<div class="data-filter-form collapse">
	<select class="form-control"></select>
	<select class="form-control"></select>
	<select class="form-control"></select>
</div>
<p>A List of all Active Samples, use Filters or Search to find old stuff.</p>
-->
</div>
</form>

<div><?= $data['page_list_html'] ?></div>

<table class="table table-sm">
<thead class="table-dark">
	<tr>
		<th>ID</th>
		<th>Product</th>
		<th>Variety</th>
		<th>Origin</th>
		<th>Options</th>
		<th class="r">Quantity</th>
		<th></th>
	</tr>
</thead>
<tbody>
<?php
foreach ($data['sample_list'] as $s) {

	// $s['id_nice'] = _nice_id($s['id'], $s['guid']);

?>
	<tr>
		<td>
			<a href="/sample/<?= $s['id'] ?>"><?= ($s['name'] ?: $s['id']) ?></a>
		</td>
		<td><?= __h($s['product_name']) ?></td>
		<td><?= __h($s['variety_name']) ?></td>
		<td><?= __h($s['source_license_name']) ?></td>
		<td>
			<!-- {{ s.meta.Lot.medically_compliant ? "Medical" }} -->
		</td>
		<td class="r">
			<?= trim(sprintf('%0.1f %s', $s['qty'], $s['meta']['Lot']['uom'])) ?>
		</td>
		<td class="r">
		<?php
		switch ($s['stat']) {
		case 100:
			echo '<button class="btn btn-sm btn-primary"><i class="far fa-check-square"></i> Accept</button>';
			// <a title="Add Results" class="btn btn-sm btn-outline-primary" href="/result/create?sample_id="><i class="fas fa-flask"></i> Add Result</i></a>
			break;
		case 200:
			printf('<a title="Add Results" class="btn btn-sm btn-primary" href="/result/create?sample_id=%s"><i class="fas fa-flask"></i> Add Result</i></a>', $s['id']);
			break;
		case 300:
		case 302:
			// View Most Recent Lab Result
			// Share?
			// printf('<a title="Add Results" class="btn btn-sm btn-primary" href="/result/create?sample_id=%s"><i class="fas fa-flask"></i> Add Result</i></a>', $s['id']);
			break;
		case 400:
			break;
		}
		?>
		</td>
	</tr>
<?php
}
?>
</tbody>
</table>
