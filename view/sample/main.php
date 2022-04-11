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
	<div>
		<a class="btn btn-outline-primary" href="/sample/create"><i class="fas fa-plus"></i></a>
	</div>
</div>


<div class="d-flex mb-2">
	<div>
		<input class="form-control" name="q" placeholder="- search -">
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

<div><?= $data['page_list_html'] ?></div>

<table class="table table-sm">
<thead class="table-dark">
	<tr>
		<th>ID</th>
		<th>Product</th>
		<th>Strain</th>
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
		<td>
			<!-- {{ s.meta.Lot.medically_compliant ? "Medical" }} -->
		</td>
		<td class="r">
			<?= trim(sprintf('%0.1f %s', $s['qty'], $s['meta']['Lot']['uom'])) ?>
		</td>

		<td class="r">
		<?php
		if ($s['meta']['Lot']['global_lab_result_id']) {
		?>
			<a class="btn btn-sm btn-outline-secondary" href="/result/{{ s.meta.Lot.global_lab_result_id }}/edit">
				<i class="fas fa-edit"></i> Edit
			</a>
			<a class="btn btn-sm btn-outline-success" href="/result/{{ s.meta.Lot.global_lab_result_id }}"><i class="fas fa-tasks"></i> View</a>
		<?php
		} else {
		?>
			<a title="Add Results" class="btn btn-sm btn-outline-primary" href="/result/create?sample_id={{ s.id }}"><i class="fas fa-flask"></i> Add Result</i></a>
		<?php
		}
		?>
		</td>
	</tr>
<?php
}
?>
</tbody>
</table>
