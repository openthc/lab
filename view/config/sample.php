<?php
/**
 *
 */

use \Edoceo\Radix\Session;

Session::flash('warn', 'Sample Sequence is Beta');

function _input_group($id_name, $v, $head=null, $tail=null)
{
	ob_start();
?>
	<div class="input-group">
		<?php
		if ($head) {
			printf('<div class="input-group-text" style="width: 6em;">%s</div>', $head);
		}
		?>
		<input class="form-control r" id="<?= $id_name ?>" min="1" name="<?= $id_name ?>" step="1" type="number" value="<?= $v ?>">
		<?php
		if ($tail) {
			echo $tail;
		}
		?>
	</div>
<?php
	return ob_get_clean();
}

?>



<style>
pre.color-invert {
	background: #333;
	color: #f3f3f3;
	padding: 0.25rem;
}
</style>

<div class="container mt-4">

	<h2>Sample Sequence Format</h2>
	<div>
		<p>You can use a custom sequence for creating Lab Sample and Result IDs</p>
	</div>

	<form method="post">
	<div class="input-group">
		<div class="input-group-prepend">
			<div class="input-group-text" style="width: 6em;">Format:</div>
		</div>
		<input class="form-control" id="lab-sample-seq-format" name="lab-sample-seq-format" value="<?= __h($data['seq_format']) ?>">
		<div class="input-group-append">
			<button class="btn btn-outline-warning" name="a" type="submit" value="update-seq-format"><i class="fas fa-save"></i></button>
		</div>
	</div>
	</form>


	<pre class="color-invert">
		{TYPE}  two character type:          "<em>LS|LR</em>"
		{YYYY}  four digit year:             "<em>{{ seq.YYYY }}</em>"
		{YY}    two digit year:              "<em>{{ seq.YY }}</em>"
		{MM}    two digit month:             "<em>{{ seq.MM }}</em>"
		{MA}    single character month:      "<em>{{ seq.MA }}</em>"
		{DD}    two digit day of month:      "<em>{{ seq.DD }}</em>"
		{DDD}   three digit day of year:     "<em>{{ seq.DDD }}</em>"
		{HH}    two digit hour:              "<em>{{ seq.HH }}</em>"
		{II}    two digit minute:            "<em>{{ seq.II }}</em>"
		{SS}    two digit seconds:           "<em>{{ seq.SS }}</em>"
		{SEQ}   sequence, global:            "<em>{{ seq.g }}</em>"
		{SEQ_Y} sequence, resets yearly      "<em>{{ seq.y }}</em>"
		{SEQ_Q} sequence, resets quarterly:  "<em>{{ seq.q }}</em>"
		{SEQ_M} sequence, resets monthly:    "<em>{{ seq.m }}</em>"</pre>
	<p>Each of the items named <code>SEQ</code> may have a numeric suffix added to indicate how many zeros to pad with</p>
	<pre class="color-invert">
		{SEQ_Y6} six digit, yearly:     "<em>{{ seq.y6 }}</em>"
		{SEQ_Q9} nine digit, quarterly:  "<em>{{ seq.q9 }}</em>"</pre>


	<h2>Current Sequence Information</h2>
	<form method="post">
	<div class="mb-2">
		<?= _input_group("seq-g-min"
			, $data['seq']['g']
			, 'Global'
			, '<button class="btn btn-outline-warning" name="a" type="submit" value="reset-seq-g"><i class="fas fa-sync"></i></button>'
		) ?>
	</div>

	<div class="mb-2">
		<?= _input_group("seq-y-min"
			, $data['seq']['y']
			, 'Yearly:'
			, '<button class="btn btn-outline-warning" name="a" type="submit" value="reset-seq-y"><i class="fas fa-sync"></i></button>'
		) ?>
	</div>

	<div class="mb-2">
		<?= _input_group("seq-q-min"
			, $data['seq']['q']
			, 'Quarterly:'
			, '<button class="btn btn-outline-warning" name="a" type="submit" value="reset-seq-q"><i class="fas fa-sync"></i></button>'
		) ?>
	</div>

	<div class="mb-2">
		<?= _input_group("seq-m-min"
			, $data['seq']['m']
			, 'Monthly:'
			, '<button class="btn btn-outline-warning" name="a" type="submit" value="reset-seq-m"><i class="fas fa-sync"></i></button>'
		) ?>
	</div>

	<div class="mb-2">
		<?= _input_group("seq-d-min"
			, $data['seq']['d']
			, 'Daily:'
			, '<button class="btn btn-outline-warning" name="a" type="submit" value="reset-seq-d"><i class="fas fa-sync"></i></button>'
		) ?>
	</div>

	</form>

</div>
