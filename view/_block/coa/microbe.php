<h3>Microbial Analysis</h3>

<table class="table table-sm">
	<thead>
		<tr>
			<th>Analyte</th>
			<th>Colony Forming Units</th>
		</tr>
	</thead>
	<tbody>
			<tr>
				<td>Bacteria</td>
				<td>
				<?= $data['Result']['microbial_aerobic_bacteria_cfu_g'] ?: '-' ?>
				cfu/g
				</td>
			</tr>
			<tr>
				<td>Coliforms</td>
				<td>
				<?= $data['Result']['microbial_total_coliform_cfu_g'] ?: '-' ?>
				cfu/g
			</td>
			</tr>
			<tr>
				<td>E.Coli</td>
				<td>
				<?= $data['Result']['microbial_pathogenic_e_coli_cfu_g'] ?: '-' ?>
				cfu/g
			</td>
			</tr>
			<tr>
				<td>Yeast Mold</td>
				<td>
				<?= $data['Result']['microbial_total_yeast_mold_cfu_g'] ?: '-' ?>
				cfu/g
			</td>
			</tr>
			<tr>
				<td>Salmonella</td>
				<td>
				<?= $data['Result']['microbial_salmonella_cfu_g'] ?: '-' ?>
				cfu/g
			</td>
			</tr>
			<!-- <tr>
				<td>Yeast</td>
				<td>
				<?= $data['Result']['microbial_total_yeast_mold_cfu_g'] ?: '-' ?>
				cfu/g
			</td>
			</tr> -->
			<tr>
				<td>Plate Count</td>
				<td>
				<?= $data['Result']['microbial_total_viable_plate_count_cfu_g'] ?: '-' ?>
				cfu/g
			</td>
			</tr>
	</tbody>
</table>

