<?php
/**
 * (c) 2018 OpenTHC, Inc.
 * This file is part of OpenTHC Lab Portal released under GPL-3.0 License
 * SPDX-License-Identifier: GPL-3.0-only
 *
 * View Client List
 */

?>

<table class="table table-sm">
<thead class="table-dark">
	<tr>
		<th>ID</th>
		<th>Name</th>
		<th class="r" colspan="2">
		</th>
	</tr>
</thead>
<tbody>
<?php
foreach ($data['license_list'] as $s) {

	$s['id_nice'] = _nice_id($s['id'], $s['guid']);

?>
	<tr>
		<td><a href="/client/<?= $s['id'] ?>"><?= $s['id_nice'] ?></a></td>
		<td><?= $s['name'] ?></td>
		<td><?= $s['type'] ?></td>
		<td class="r"><?= $s['status_html'] ?></td>
		<td class="r">
			<a class="btn btn-sm btn-outline-secondary" href="/pub/<?= $s['id'] ?>.html" target="_blank"><i class="fas fa-share-alt"></i></a>
		</td>
	</tr>
<?php
}
?>
</tbody>
</table>
