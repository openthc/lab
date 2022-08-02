<?php
/**
 * Show List of Report
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

use OpenTHC\Lab\Lab_Report;

$search_data = [];
$search_data['search_field_list'] = [
	'Report ID',
	'Sample ID',
	'Origin',
	'Variety',
];

?>

<h1>Reports</h1>

<?= $this->block('search-filter', $search_data); ?>

<table class="table table-sm">
<thead class="table-dark">
	<tr>
		<th>Date</th>
		<th>Report</th>
		<th>Sample</th>
		<th>Inventory</th>
		<th>Client</th>
		<th>Type</th>
		<th class="c">Status</th>
		<th class="r" colspan="2"></th>
	</tr>
</thead>
<tbody>
<?php
foreach ($data['report_list'] as $s) {

	$s['id_nice'] = $s['guid'] ?: $s['id'];

	$dt = new DateTime($s['created_at'], new DateTimezone($_SESSION['tz']));

?>
	<tr>
		<td title="<?= $s['created_at'] ?>"><?= $dt->format('m/d/y H:i') ?></td>
		<td><a href="/report/<?= $s['id'] ?>"><?= $s['name'] ?></a></td>
		<td><?php
		if ( ! empty($s['lab_sample_id'])) {
			printf('<a href="/sample/%s">%s</a>', $s['lab_sample_id'], __h($s['lab_sample_guid']));
		} else {
			echo '-';
		}
		?></td>
		<td><a href="/inventory/<?= $s['inventory_id'] ?>"><?= __h($s['inventory_guid']) ?></a></td>
		<td><?= __h($s['client_license_name']) ?></td>
		<td><?= $s['type_nice'] ?></td>
		<td class="r"><?= $s['status_html'] ?></td>
		<td class="r">
			<form action="/report/<?= $s['id'] ?>" method="post">
			<div class="btn-group btn-group-sm">
			<?php
			if ($s['flag'] & Lab_Report::FLAG_PUBLIC) {
				echo '<button class="btn btn-outline-success" formtarget="_blank" name="a" title="Lab Reports Published, click to re-publish &amp; view" type="submit" value="lab-report-share"><i class="fas fa-share-alt"></i> Share</button>';
			} else {
				echo '<button class="btn btn-primary" formtarget="_blank" name="a" title="Lab Results NOT Published, Click to Publish" type="submit" value="lab-report-share"><i class="fas fa-share-alt"></i> Share</button>';
			}
			?>
			<a class="btn btn-sm btn-outline-secondary"
				href="/pub/<?= $s['id'] ?>.html"
				target="_blank"
				title="<?= _('View the published public data') ?>"
				><i class="fas fa-share-alt"></i></a>
			</div>
			</form>
		</td>
	</tr>
<?php
}
?>
</tbody>
</table>

<div class="ms-2">

	<div><?= $data['page_list_html'] ?></div>

	<div class="btn-group">
		<a class="btn btn-outline-secondary" href="?<?= http_build_query(array_merge($_GET, [ 'p' => $data['result_page']['older'] ])) ?>"><i class="fas fa-arrow-left"></i></a>
		<a class="btn btn-outline-secondary" href="?<?= http_build_query(array_merge($_GET, [ 'p' => $data['result_page']['newer'] ])) ?>"><i class="fas fa-arrow-right"></i></a>
	</div>
</div>


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
