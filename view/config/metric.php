<?php
/**
 * View and Edit the Metrics
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

use OpenTHC\Lab\Lab_Metric;
use OpenTHC\Lab\UOM;

function _draw_select_uom($id, $uom_want)
{

	$name = $id;

	$html = [];
	$html[] = sprintf('<select class="form-control form-control-sm" id="%s" name="%s">', $id, $name);

	foreach (UOM::$uom_list as $v => $n) {
		$sel = ($v == $uom_want ? ' selected' : null);
		$html[] = sprintf('<option%s value="%s">%s</option>', $sel, $v, $n);
	}

	$html[] = '</select>';
	return implode('', $html);
}

?>

<style>
.table-metric label {
	display: block;
	margin: 0;
}
.table-metric td {
	font-size: 120%;
}
.table-metric th h3 {
	margin: 1rem 0 0 0;
}
.table-metric td label {
	user-select: none;
}
.table-metric td input.form-control-sm {
	width: 8em;
}
</style>

<div class="container mt-2">

<h1><a href="/config">Config</a> :: Metrics</h1>
<p>Configure which metrics are used with which product classes.</p>

<form autocomplete="off" method="post">
<table class="table table-sm table-bordered table-metric">
<?php
foreach ($this->data['metric_list'] as $m) {

	if ($m['type'] != $type_x) {
	?>
		<tr class="thead-dark">
			<th colspan="7"><h3><?= h($m['type']) ?></h3></th>
		</tr>
		<tr class="thead-dark">
			<th>Name</th>
			<th>UOM</th>
			<th>LOD</th>
			<th>LOQ-LB</th>
			<th>LOQ-UB</th>
			<th>Limit</th>
			<th></th>
		</tr>
	<?php
	}

?>
	<tr>
		<td><?= h($m['name']) ?></td>
		<?php
		if (308 == $m['stat']) {
			printf('<td colspan="5">%s</td>', $m['meta']['goto']);
		} else {
		?>
		<td><?= __h($m['meta']['uom']) ?></td>
		<td class="r"><?= __h($m['meta']['lod']) ?></td>
		<td class="r"><?= h($m['meta']['loq-lb']) ?></td>
		<td class="r"><?= h($m['meta']['loq-lb']) ?></td>
		<td class="r"><?= h($m['meta']['max']['val']) ?></td>
		<?php
		}
		?>
		<td class="r">
			<div class="btn-group btn-group-sm">
			<?php
			switch ($m['stat']) {
				case 200:
					printf('<a class="btn btn-primary" href="/config/metric?id=%s" title="Edit Product Classes"><i class="far fa-edit"></i></a>', $m['id']);
					printf('<button class="btn btn-success btn-metric-mute-toggle" data-lab-metric-id="%s" type="button" value="hide"><i class="far fa-circle"></i></button>', $m['id']);
					break;
				case 308:
					echo '<button class="btn btn-outline-secondary disabled" disabled type="button"><i class="fas fa-link"></i></button>';
					break;
				case 410:
					printf('<button class="btn btn-secondary btn-metric-mute-toggle" data-lab-metric-id="%s" type="button" value="show"><i class="fas fa-ban"></i></button>', $m['id']);
					break;
				default:
					echo $m['stat'];
			}
			?>
			</div>
		</td>
	</tr>

	<?php
	$type_x = $m['type'];
	?>

<?php
}
?>
</table>

<div class="form-actions">
	<button class="btn btn-primary" name="a" value="save"><i class="fas fa-save"></i> Save</button>
</div>
</form>

</div>


<script>
$(function() {
	$('.table-metric').on('click', '.btn-metric-mute-toggle', function() {
		var $td = $(this).parent();
		var arg = {
			a: 'lab-metric-mute-toggle',
			lab_metric_id: this.getAttribute('data-lab-metric-id'),
			v: this.value
		};
		$.post('/config/metric', arg).then(function(body, b) {
			$td.empty();
			$td.append(body);
		});
	});
});
</script>
