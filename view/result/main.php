<?php
/**
 * Show List of Result Objects
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

?>

<div class="d-flex justify-content-between">
	<div>
		<h1>Results</h1>
	</div>
	<div class="pt-2">
		<a class="btn btn-outline-secondary" href="/result/upload"><i class="fas fa-cloud-upload-alt"></i></a>
	</div>
</div>

<?= $this->block('search-filter', [
	'search_page' => $data['search_page'],
	'search_field_list' => [
		'Result ID',
		'Sample ID',
		'Origin',
		'Variety',
	]
]); ?>

<!-- <p>A List of all Active and Recent Results, use Filters or Search to find old stuff.</p> -->

<table class="table table-sm">
<thead class="table-dark">
	<tr>
		<th>Result ID</th>
		<th>Sample ID</th>
		<th>Inventory ID</th>
		<th>Date</th>
		<th>Type</th>
		<th class="c">Status</th>
		<th class="r" colspan="2">
			<!-- Send them to dump.openthc -->
			<!-- <a class="btn btn-sm btn-outline-secondary" href="/result/download"><i class="fas fa-download"></i> -->
		</th>
	</tr>
</thead>
<tbody>
<?php
foreach ($data['result_list'] as $s) {

	$s['id_nice'] = $s['guid'] ?: $s['id'];

?>
	<tr>
		<td><a href="/result/<?= $s['id'] ?>"><?= $s['id_nice'] ?></a></td>
		<td><?php
		if ( ! empty($s['lab_sample_id'])) {
			printf('<a href="/sample/%s">%s</a>', $s['lab_sample_id'], __h($s['lab_sample_guid']));
		} else {
			echo '-';
		}
		?></td>
		<td><a href="/inventory/<?= $s['inventory_id'] ?>"><?= __h($s['inventory_guid']) ?></a></td>
		<td><?= $s['created_at'] ?></td>
		<td><?= __h($s['name']) ?></td>
		<td class="r"><?= $s['status_html'] ?></td>
		<td class="r">
			<form action="" autocomplete="off" method="post">
			<div class="btn-group btn-group-sm">
			<a class="btn btn-sm btn-outline-secondary" href="<?= sprintf('/result/%s/update', $s['id']) ?>"><i class="fas fa-edit"></i></a>
			<?php
			// if (100 == $s['stat']) {
				// echo '<button class="btn btn-sm btn-outline-secondary" type="button"><i class="fa-solid fa-flag-checkered"></i></button>';
			// }
			?>
			<!-- <a class="btn btn-sm btn-outline-secondary"
				href="/pub/<?= $s['id'] ?>.html"
				target="_blank"
				title="<?= _('View the published public data') ?>"
				><i class="fas fa-share-alt"></i></a> -->
			</div>
			</form>
		</td>
	</tr>
<?php
}
?>
</tbody>
</table>

<!--
	Pager....
 -->
<div class="ms-2">
		<div class="btn-group">
			<a class="btn btn-outline-secondary" href="?<?= http_build_query(array_merge($_GET, [ 'p' => $data['search_page']['older'] ])) ?>"><i class="fas fa-arrow-left"></i></a>
			<a class="btn btn-outline-secondary" href="?<?= http_build_query(array_merge($_GET, [ 'p' => $data['search_page']['newer'] ])) ?>"><i class="fas fa-arrow-right"></i></a>
		</div>
</div>


<script>
var sync_base = '/result/';
var sync_wait = 500;

function syncExec($b, cbf)
{
	$b.addClass('btn-outline-danger');
	$b.find('i').addClass('fa-spin');
	$b.data('sync', '1');

	var arg = {
		a: 'sync',

	};

	$.post(sync_base + $b.data('id'), arg, function() {
		$b.find('i').removeClass('fa-spin');
		$b.removeClass('btn-outline-warning btn-outline-danger');
		$b.addClass('btn-outline-secondary');
		if ((cbf) && (typeof cbf === 'function')) {
			cbf();
		}
	});

}

// Find One
function syncFind()
{
	var $b = null;

	$('.btn-sync').each(function(i, n) {
		if ('0' == $(n).data('sync')) {
			$b = $(n);
			return(false);
		}
	});

	return $b;

}


function syncPump()
{
	var $b = syncFind();
	if ($b) {
		syncExec($b, function() {
			setTimeout(syncPump, sync_wait);
		});
	}

}


$(function() {

	$('.btn-sync').on('click', function() {
		var $b = $(this);
		syncExec($b);
	});

	setTimeout(syncPump, sync_wait);

});
</script>
