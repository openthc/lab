<?php
/**
 * Show List of Report
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */


$search_data = [];
$search_data['search_field_list'] = [
	'Report ID',
	'Sample ID',
	'Origin',
	'Variety',
];

?>

<h1>Reports</h1>

<?php
// $this->block('search-filter', $search_data);
?>

<!-- <div class="d-flex mb-2">
	<div>
		<input class="form-control" name="q" placeholder="- search -">
	</div>
	<div class="ms-2">
		<div class="btn-group">
			<a class="btn btn-outline-secondary" href="?stat=100">Pending: <?= $data['report_stat']['100'] ?></a>
			<a class="btn btn-outline-primary" href="?stat=200">Passed: <?= $data['report_stat']['200'] ?></a>
			<a class="btn btn-outline-danger" href="?stat=400">Failed: <?= $data['report_stat']['400'] ?></a>
			<a class="btn btn-outline-secondary" href="?stat=*">All</a>
		</div>
	</div>
	<div class="ms-2">
		<div class="btn-group">
			<a class="btn btn-outline-secondary" href="?p=<?= ($data['report_page']['older']) ?>"><i class="fas fa-arrow-left"></i></a>
			<a class="btn btn-outline-secondary" href="?p=<?= ($data['report_page']['newer']) ?>"><i class="fas fa-arrow-right"></i></a>
		</div>
	</div>
</div> -->

<!-- <p>A List of all Active and Recent Reports, use Filters or Search to find old stuff.</p> -->

<table class="table table-sm">
<thead class="table-dark">
	<tr>
		<th>Report ID</th>
		<th>Sample ID</th>
		<th>Inventory ID</th>
		<th>Date</th>
		<th>Type</th>
		<!-- <th class="r">THC</th>
		<th class="r">CBD</th> -->
		<th class="c">Status</th>
		<th class="r" colspan="2">
			<!-- Send them to dump.openthc -->
			<!-- <a class="btn btn-sm btn-outline-secondary" href="/report/download"><i class="fas fa-download"></i> -->
		</th>
	</tr>
</thead>
<tbody>
<?php
foreach ($data['report_list'] as $s) {

	$s['id_nice'] = $s['guid'] ?: $s['id'];

?>
	<tr>
		<td><a href="/report/<?= $s['id'] ?>"><?= $s['id_nice'] ?></a></td>
		<td><?php
		if ( ! empty($s['lab_sample_id'])) {
			printf('<a href="/sample/%s">%s</a>', $s['lab_sample_id'], __h($s['lab_sample_guid']));
		} else {
			echo '-';
		}
		?></td>
		<td><a href="/inventory/<?= $s['inventory_id'] ?>"><?= __h($s['inventory_guid']) ?></a></td>
		<td><?= $s['created_at'] ?></td>
		<td><?= $s['type_nice'] ?></td>
		<td><?= $s['medically_compliant'] ? "Medical" : '' ?></td>
		<td class="r"><?= $s['thc'] ?></td>
		<td class="r"><?= $s['cbd'] ?></td>
		<td class="r"><?= $s['status_html'] ?></td>
		<td class="r">
			<a class="btn btn-sm btn-outline-secondary"
				href="/pub/<?= $s['id'] ?>.html"
				target="_blank"
				title="<?= _('View the published public data') ?>"
				><i class="fas fa-share-alt"></i></a>
		</td>
	</tr>
<?php
}
?>
</tbody>
</table>


<script>
var sync_base = '/report/';
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
