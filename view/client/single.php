<?php
/**
 * Render a Lab Result
 */

?>


<div class="row mt-4">
<div class="col-md-6">
	<h1>Client: <?= __h($data['Client']['name']) ?></h1>
</div>
</div>

<table class="table">
<thead class="thead-dark">
	<tr>
		<th>Sample</th>
		<th>Result</th>
		<th>Product</th>
		<th>THC</th>
		<th>CBD</th>
	</tr>
</thead>
<tbody>
<?php
foreach ($data['lab_result_list'] as $lr) {
?>
	<tr>
		<td><?= __h($lr['id']) ?></td>
	</tr>
<?php
}
?>
</tbody>
</table>
