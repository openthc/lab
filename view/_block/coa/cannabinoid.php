<h3>Cannabinoid Profile</h3>
	<!-- <div class="row cards">
		<div class="col-sm-4">
			<div class="h3">
				{{ Result.Cannabinoid['018NY6XC00LM49CV7QP9KM9QH9'].qom }}
			</div>
			Total THC
		</div>
		<div class="col-sm-4">
			<div class="h3">
				{{ Result.Cannabinoid['018NY6XC00LMK7KHD3HPW0Y90N'].qom }}
			</div>
			Total CBD
		</div>
		<div class="col-sm-4">
			<h3>{{ sum }}</div>
			Total Cannabinoids
		</div>
	</div>
</div> -->
<table class="table table-sm">
	<thead>
		<tr>
			<th>Analyte</th>
			<th>Mass Concentration</th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($data['MetricList']['Cannabinoid'] as $k => $v) {
		?>
			<tr>
				<!-- <td>{{ k }}</td> -->
				<td><?= __h($m['name']) ?></td>
				<td class="r"><?= __h($m['qom']) ?></td>
			</tr>
		<?php
		}
		?>
	</tbody>
</table>
