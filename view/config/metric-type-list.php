<?php
/**
 * View and Edit the Metric Types
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

use OpenTHC\Lab\Lab_Metric;
use OpenTHC\Lab\UOM;
?>

<h1><a href="/config">Config</a> :: Metric :: Types</h1>
<p>Use the form below to select all the product types that apply for a given Metric Type group.</p>


<form autocomplete="off" method="post">
<?php
foreach ($data['Metric_Type_list'] as $mt) {

	$mt_meta = json_decode($mt['meta'], true);
	$pt_matrix = $mt_meta['product-type-matrix'];

?>
	<section id="metric-type-wrap">
	<div class="d-flex justify-content-between">
		<div>
			<h2>
				<?= __h($mt['name']) ?>
			</h2>
		</div>
		<div class="r">
			<button class="btn btn-outline-secondary"
				aria-expanded="true"
				data-bs-toggle="collapse"
				data-bs-target="#<?= sprintf('metric-type-%s-wrap', $mt['id']) ?>"
				type="button"
			><i class="fa-solid fa-minimize"></i></button>
		</div>
	</div>


	<p>Enable / disable this Metric for specific Product Types</p>

	<div class="collapse showd" id="<?= sprintf('metric-type-%s-wrap', $mt['id']) ?>">
	<div class="result-metric-wrap">
	<?php
	foreach ($data['Product_Type_list'] as $pt_ulid => $pt_name) {

		$key = sprintf('mt%s-pt%s', $mt['id'], $pt_ulid);

		echo '<div class="result-metric-data">';
			echo '<div class="input-group">';
				echo '<div class="input-group-text">';
				printf('<input %s id="%s" name="%s" type="checkbox" value="1">'
					, ($pt_matrix[$pt_ulid] ? 'checked' : '')
					, $key
					, $key
				);
				echo '</div>';
				printf('<label class="form-control" for="%s">%s</label>', $key, __h($pt_name));
			echo '</div>';
		echo '</div>';
	}
	?>
	</div>
	</div>
	</section>

	<hr>

<?php
}
?>

<div class="form-actions">
	<button class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
</div>

</form>


<script>
$(function() {
	// $('.collapse.result-metric-wrap').on('hide.bs.collapse', function(e) {
	// 	debugger;
	// });

	// $('.collapse.result-metric-wrap').on('show.bs.collapse', function(e) {
	// 	debugger;
	// });
});
</script>
