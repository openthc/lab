<?php
/**
 * View Sample Sequence Settings
 *
 * SPDX-License-Identifier: GPL-3.0-only
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

	<form autocomplete="off" method="post">
	<div class="input-group">
		<div class="input-group-text" style="width: 6em;">Format:</div>
		<input class="form-control" id="lab-sample-seq-format" name="lab-sample-seq-format" value="<?= __h($data['seq_format']) ?>">
		<button class="btn btn-warning" name="a" type="submit" value="update-seq-format"><i class="fas fa-save"></i></button>
	</div>
	<div class="input-group">
		<div class="input-group-prepend">
			<div class="input-group-text" style="width: 6em;">Next:</div>
		</div>
		<input class="form-control" id="lab-sample-seq-peek" name="lab-sample-seq-peek" value="<?= __h($data['seq_peek']) ?>">
		<div class="input-group-append">
			<!-- <button class="btn btn-warning" name="" type="submit" value=""><i class="fas fa-save"></i></button> -->
		</div>
	</div>
	</form>


	<pre class="color-invert">
		{TYPE}  two character type:          &quot;<em>LS|LR</em>&quot;
		{YYYY}  four digit year:             &quot;<em><?= $data['seq']['YYYY'] ?></em>&quot; [2000-9999]
		{YY}    two digit year:              &quot;<em><?= $data['seq']['YY'] ?></em>&quot;   [20-99]
		{MM}    two digit month:             &quot;<em><?= $data['seq']['MM'] ?></em>&quot;   [01-12]
		{MA}    single character month:      &quot;<em><?= $data['seq']['MA'] ?></em>&quot;    [A-L]
		{DD}    two digit day of month:      &quot;<em><?= $data['seq']['DD'] ?></em>&quot;   [00-31]
		{DDD}   three digit day of year:     &quot;<em><?= $data['seq']['DDD'] ?></em>&quot;  [000-366]
		{HH}    two digit hour:              &quot;<em><?= $data['seq']['HH'] ?></em>&quot;   [00-23]
		{II}    two digit minute:            &quot;<em><?= $data['seq']['II'] ?></em>&quot;   [00-59]
		{SS}    two digit seconds:           &quot;<em><?= $data['seq']['SS'] ?></em>&quot;   [00-59]
		{SEQ}   sequence, global:            &quot;<em><?= $data['seq']['g'] ?></em>&quot;
		{SEQ_Y} sequence, resets yearly      &quot;<em><?= $data['seq']['y'] ?></em>&quot;
		{SEQ_Q} sequence, resets quarterly:  &quot;<em><?= $data['seq']['q'] ?></em>&quot;
		{SEQ_M} sequence, resets monthly:    &quot;<em><?= $data['seq']['m'] ?></em>&quot;
		{SEQ_D} sequence, resets daily:      &quot;<em><?= $data['seq']['d'] ?></em>&quot;</pre>
	<p>Each of the items named <code>SEQ</code> may have a numeric suffix added to indicate how many zeros to pad with</p>
	<pre class="color-invert">
		{SEQ_Y6} six digit, yearly:      &quot;<em><?= $data['seq']['y6'] ?></em>&quot;
		{SEQ_Q9} nine digit, quarterly:  &quot;<em><?= $data['seq']['q9'] ?></em>&quot;</pre>


	<h2>Current Sequence Information</h2>
	<form autocomplete="off" method="post">
	<div class="mb-2">
		<?= _input_group("seq-g-min"
			, $data['seq']['g']
			, 'Global'
			, '<button class="btn btn-warning" name="a" type="submit" value="reset-seq-g"><i class="fas fa-sync"></i></button>'
		) ?>
	</div>

	<div class="mb-2">
		<?= _input_group("seq-y-min"
			, $data['seq']['y']
			, 'Yearly:'
			, '<button class="btn btn-warning" name="a" type="submit" value="reset-seq-y"><i class="fas fa-sync"></i></button>'
		) ?>
	</div>

	<div class="mb-2">
		<?= _input_group("seq-q-min"
			, $data['seq']['q']
			, 'Quarterly:'
			, '<button class="btn btn-warning" name="a" type="submit" value="reset-seq-q"><i class="fas fa-sync"></i></button>'
		) ?>
	</div>

	<div class="mb-2">
		<?= _input_group("seq-m-min"
			, $data['seq']['m']
			, 'Monthly:'
			, '<button class="btn btn-warning" name="a" type="submit" value="reset-seq-m"><i class="fas fa-sync"></i></button>'
		) ?>
	</div>

	<div class="mb-2">
		<?= _input_group("seq-d-min"
			, $data['seq']['d']
			, 'Daily:'
			, '<button class="btn btn-warning" name="a" type="submit" value="reset-seq-d"><i class="fas fa-sync"></i></button>'
		) ?>
	</div>

	</form>

</div>
