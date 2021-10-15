<?php
/**
 * Show the QA Results
 */

function _nice_uom($uom)
{
	switch ($uom) {
		case 'pct':
			return '&percnt;';
		case 'mg_g':
			return 'mg/g';
		case 'cfu/g':
			return 'cfu/g';
		case 'aw':
			return 'a<sub>w</sub>';
		case 'ppm':
			return 'ppm';
	}

	return '-unknown-';

}

function _qa_testing_status($obj)
{
	ob_start();
?>
<div class="form-group">
	<label><?= __h($obj['name']) ?></label>
	<select class="form-control" name="<?= $obj['id'] ?>">
		<option value="-"> -- </option>
		<option value="1">Fail</option>
		<option value="0">Pass</option>
	</select>
</div>
<?php
	return ob_get_clean();
}


// [ "fail", "na", "nd", "nr", "nt", "pass" ]
function _lrm_quick_fill()
{
	ob_start();
?>
	<div class="align-self-stretch" style="flex: 1 1 33%;">
		<div class="form-group mb-2 mx-2">
			<label></label>
			<div class="input-group">
				<div class="btn-group">
					<button class="btn btn-outline-secondary btn-bulk-nt" type="button">N/T</button>
					<button class="btn btn-outline-secondary btn-bulk-na" type="button">N/A</button>
					<button class="btn btn-outline-secondary btn-bulk-na" type="button">N/D</button>
					<button class="btn btn-outline-secondary btn-bulk-na" type="button">N/R</button>
					<button class="btn btn-outline-secondary btn-bulk-az" type="button">Zero</button>
				</div>
			</div>
		</div>
	</div>
<?php
	return ob_get_clean();
}


function _lrm_num_input($l, $n, $obj)
{
	ob_start();
?>
	<div class="form-group mb-2 mx-2">
		<label><?= $l ?></label>
		<div class="input-group">
			<select class="form-control metric-status">
				<option>OK</option>
				<option value="-1">N/A</option>
				<option value="-2">N/D</option>
				<option vlaue="-3">N/T</option>
			</select>
			<input class="form-control r metric-value"
				disabled
				name="<?= $n ?>"
				placeholder="eg: <?= __h($obj['meta']['hint']) ?>"
				type="text"
				value="<?= $obj['result'] ?>">
			<div class="input-group-text" style="min-width:4em;"><?= _nice_uom($obj['meta']['uom']) ?></div>
		</div>
	</div>
<?php
	return ob_get_clean();
}


/**
 *
 */
function _qa_section_head($t, $id)
{
	ob_start();

	echo '<h3>';

	echo '<button';
		echo ' class="btn btn-sm btn-outline-secondary btn-showhide"';
		echo ' data-bs-toggle="collapse"';
		printf(' data-bs-target="#%s"', $id);
		echo ' type="button"';
	echo '>';
	echo '<i class="fas fa-compress-arrows-alt"></i>';
	printf('</button> %s</h3>', $t);

	return ob_get_clean();
}

?>

<style>
section {
	border: 1px inset #999;
	border-radius: 0.25em;
	margin: 0 0 2em 0;
	padding: 0.75em;
}
</style>


<section>
<?= _qa_section_head("General", "metric-wrap-general") ?>
<div class="collapse metric-wrap show" id="metric-wrap-general">
<div class="d-flex flex-row flex-wrap align-items-stretch">

	<?php
	foreach ($data['MetricList']['General'] as $mid => $metric) {
	?>
		<div class="col-md-3">
			<?php
			if ('bool' == $metric['meta']['uom']) {
				// These test results are pass/fail
				echo _qa_testing_status($metric);
			} else {
				echo _lrm_num_input($metric['name'], $metricId, $metric);
			}
			?>
		</div>
	<?php
	}
	?>

</div>
</div> <!-- /#metric-wrap-general -->
</section>


<!--
	Cannabinoids
-->
<section>
<?= _qa_section_head("Cannabinoids", "metric-wrap-cb") ?>
<div class="collapse metric-wrap" id="metric-wrap-cb">
<div class="d-flex flex-row flex-wrap align-items-stretch">

	<?php
	foreach ($data['MetricList']['Cannabinoid'] as $mid => $metric) {
	?>
		<div class="align-self-stretch" style="flex: 1 1 33%;">
			<?= _lrm_num_input($metric['name'], $mid, $metric) ?>
		</div>
	<?php
	}
	?>

</div>
</div> <!-- /.metric-wrap-->
</section>


<!--
	Terpenes
-->
<section>
<?= _qa_section_head("Terpenes", "metric-wrap-t") ?>
<div class="collapse metric-wrap" id="metric-wrap-t">
<div class="d-flex flex-row flex-wrap align-items-stretch">

	<?php
	foreach ($data['MetricList']['Terpene'] as $mid => $metric) {
	?>
		<div class="align-self-stretch" style="flex: 1 1 33%;">
			<?= _lrm_num_input($metric['name'], $mid, $metric) ?>
		</div>
	<?php
	}
	?>

</div>
</div> <!-- /#metric-wrap -->
</section>


<!--
	Microbes
-->
<section>
<?= _qa_section_head("Microbes", "metric-wrap-m") ?>
<div class="collapse metric-wrap" id="metric-wrap-m">
<div class="d-flex flex-row flex-wrap align-items-stretch">

	<?= _lrm_quick_fill() ?>

	<?php
	foreach ($data['MetricList']['Microbe'] as $mid => $metric) {
	?>
		<div class="align-self-stretch" style="flex: 1 1 33%;">
			<?= _lrm_num_input($metric['name'], $mid, $metric) ?>
		</div>
	<?php
	}
	?>

</div>
</div> <!-- /#metric-wrap -->
</section>


<!--
	Mycotoxin
-->
<section>
<?= _qa_section_head("Mycotoxin", "metric-wrap-mycotoxin") ?>
<div class="collapse metric-wrap" id="metric-wrap-mycotoxin">
<div class="d-flex flex-row flex-wrap align-items-stretch">

	<?= _lrm_quick_fill() ?>

	<?php
	foreach ($data['MetricList']['Mycotoxin'] as $mid => $metric) {
	?>
		<div class="align-self-stretch" style="flex: 1 1 33%;">
			<?= _lrm_num_input($metric['name'], $mid, $metric) ?>
		</div>
	<?php
	}
	?>

</div>
</div> <!-- /#metric-wrap-s -->
</section>


<!--
	Pesticides
-->
<section>
<?= _qa_section_head("Pesticides", "metric-wrap-p") ?>
<div class="collapse metric-wrap" id="metric-wrap-p">
<div class="d-flex flex-row flex-wrap align-items-stretch">

	<?= _lrm_quick_fill() ?>

	<?php
	foreach ($data['MetricList']['Pesticide'] as $mid => $metric) {
	?>
		<div class="align-self-stretch" style="flex: 1 1 33%;">
			<?= _lrm_num_input($metric['name'], $metric['id'], $metric) ?>
		</div>
	<?php
	}
	?>

</div>
</div> <!-- /#metric-wrap-p -->
</section>


<!--
	Heavy Metal
-->
<section>
<?= _qa_section_head("Heavy Metals", "metric-wrap-hm") ?>
<div class="collapse metric-wrap" id="metric-wrap-hm">
<div class="d-flex flex-row flex-wrap align-items-stretch">

	<?= _lrm_quick_fill() ?>

	<?php
	foreach ($data['MetricList']['Metal'] as $mid => $metric) {
	?>
		<div class="align-self-stretch" style="flex: 1 1 33%;">
			<?= _lrm_num_input($metric['name'], $metric['id'], $metric) ?>
		</div>
	<?php
	}
	?>

</div>
</div>
</section>


<!--
	Solvents
-->
<section>
<?= _qa_section_head("Solvents", "metric-wrap-s") ?>
<div class="collapse metric-wrap" id="metric-wrap-s">
<div class="d-flex flex-row flex-wrap align-items-stretch">

	<?= _lrm_quick_fill() ?>

	<?php
	foreach ($data['MetricList']['Solvent'] as $mid => $metric) {
	?>
		<div class="align-self-stretch" style="flex: 1 1 33%;">
			<?= _lrm_num_input($metric['name'], $metric['id'], $metric) ?>
		</div>
	<?php
	}
	?>

</div>
</div> <!-- /#metric-wrap-s -->
</section>
