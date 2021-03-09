<h3>Terpene Profile</h3>
<?php
if (empty($data['MetricList']['Terpene'])) {
	echo '<div class="alert alert-secondary">No Terpene data entered</div>';
	return(0);
}
?>

<table class="table table-sm">
	<thead>
		<tr>
			<th>Analyte</th>
			<th>Mass Concentration</th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($data['MetricList']['Terpene'] as $k => $v) {
		?>
			<tr>
				<td><?= __h($m['name']) ?></td>
				<td class="r"><?= __h($m['qom']) ?></td>
			</tr>
		<?php
		}
		?>
	</tbody>
</table>

