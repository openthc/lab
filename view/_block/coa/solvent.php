<h3>Solvent Analysis</h3>

<table class="table table-sm">
	<thead>
		<tr>
			<th>Analyte</th>
			<th>Mass Concentration</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>Acetone</td>
			<td><?= $data['Result']['meta']['solvent_acetone_ppm'] ?: '-' ?> ppm</td>
		</tr>
		<tr>
			<td>Benzene</td>
			<td><?= $data['Result']['meta']['solvent_benzene_ppm'] ?: '-' ?> ppm</td>
		</tr>
		<tr>
			<td>Butane</td>
			<td><?= $data['Result']['meta']['solvent_butanes_ppm'] ?: '-' ?> ppm</td>
		</tr>
		<tr>
			<td>Chloroform</td>
			<td><?= $data['Result']['meta']['solvent_chloroform_ppm'] ?: '-' ?> ppm</td>
		</tr>
		<!-- <tr>
			<td>Ethane</td>
			<td> ppm</td>
		</tr> -->
		<tr>
			<td>Ethanol</td>
			<td><?= $data['Result']['meta']['solvent_methanol_ppm'] ?: '-' ?> ppm</td>
		</tr>
		<tr>
			<td>Heptane</td>
			<td><?= $data['Result']['meta']['solvent_heptane_ppm'] ?: '-' ?> ppm</td>
		</tr>
		<tr>
			<td>Hexane</td>
			<td><?= $data['Result']['meta']['solvent_hexanes_ppm'] ?: '-' ?> ppm</td>
		</tr>
		<!-- <tr>
			<td>Isobutane</td>
			<td> ppm</td>
		</tr> -->
		<!-- <tr>
			<td>Isopentane</td>
			<td> ppm</td>
		</tr> -->
		<tr>
			<td>Isopropanol</td>
			<td><?= $data['Result']['solvent_isopropanol_ppm'] ?: '-' ?> ppm</td>
		</tr>
		<tr>
			<td>Pentane</td>
			<td><?= $data['Result']['solvent_pentanes_ppm'] ?: '-' ?> ppm</td>
		</tr>
		<tr>
			<td>Propane</td>
			<td><?= $data['Result']['solvent_propane_ppm'] ?: '-' ?> ppm</td>
		</tr>
		<tr>
			<td>Toluene</td>
			<td><?= $data['Result']['solvent_toluene_ppm'] ?: '-' ?> ppm</td>
		</tr>
	</tbody>
</table>

