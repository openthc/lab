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

<?= $this->block('search-filter', [
	'search_page' => $data['search_page'],
	'search_field_list' => [
		'Sample ID',
		'Origin'
	]
]); ?>

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

<div><?= $data['page_list_html'] ?></div>
