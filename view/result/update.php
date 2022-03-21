<?php
/**
 * Lab Result Update
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

use Edoceo\Radix\DB\SQL;

?>

<h1><a href="/result">Result</a> :: Update</h1>

<style>
.lab-metric-grid {
	display: flex;
	flex-direction: row;
	flex-wrap: wrap;
	/* justify-content: space-between; */
	margin: 0 -0.25rem;
}
.lab-metric-item {
	flex: 1 0 33.3333%;
	/* max-width: 30rem; */
	min-width: 15rem;
	padding: 0.25rem;
}
.lab-metric-item .input-group-prepend .input-group-text {
	display: block;
	overflow: hidden;
	text-align: left;
	text-overflow: ellipsis;
	white-space: nowrap;
	width: 10em;
}
.lab-metric-item .input-group-prepend .input-group-text:hover {
	min-width: 10em;
	width: auto;
}
.lab-metric-item::after {
	content: '';
	flex: auto;
}
</style>

<form action="" autocomplete="off" method="post">
<div class="container">
<section style="margin-bottom:1rem;">

	<div class="row">
		<div class="col-md-6">
			<div class="input-group mb-2">
				<div class="input-group-text">
					<a href="/sample/<?= $data['Sample']['id'] ?>">Sample:</a>
				</div>
				<input class="form-control" readonly value="<?= __h($data['Sample']['name']) ?>">
			</div>
		</div>
		<div class="col-md-6">
			<div class="input-group mb-2">
				<div class="input-group-text">
					<a href="/report/license?id=<?= $data['License_Source']['id'] ?>">License:</a>
				</div>
				<input class="form-control" readonly value="<?= __h($data['License_Source']['name']) ?>">
			</div>
		</div>
	</div>


	<div class="row">
		<div class="col-md-4">
			<div class="input-group mb-2">
				<div class="input-group-text">Lot:</div>
				<input class="form-control" readonly value="<?= __h($data['Lot']['guid']) ?>">
			</div>
		</div>
		<div class="col-md-4">
			<div class="input-group mb-2">
				<div class="input-group-text">Product:</div>
				<input class="form-control" readonly value="<?= __h($data['Product']['name']) ?> [<?= basename($data['Product_Type']['name']) ?>]">
			</div>
		</div>
		<div class="col-md-4">
			<div class="input-group mb-2">
				<div class="input-group-text">Variety:</div>
				<input class="form-control" readonly value="<?= __h($data['Variety']['name']) ?>">
			</div>
		</div>
	</div>

	<div class="row">

		<h3><i class="far fa-comments"></i> Terp Notes:</h3>
		<div class="input-group">
			<textarea class="form-control" id="lab-result-terp-note" name="terp-note"><?= __h($Lab_Result['note']) ?></textarea>
			<button class="btn btn-outline-secondary btn-terp-note-auto" type="button"><i class="fas fa-magic"></i> Auto</button>
		</div>

	</div>

</section>

<?php
foreach ($data['Result_Metric_Group_list'] as $lms_id => $lms) {
?>
	<section style="margin-bottom:1rem;">

		<div class="d-flex justify-content-between">
			<div>
				<h2><?= __h($lms['name']) ?></h2>
			</div>
			<?php
			if ('General' != $lms['name']) {
			?>
				<div>
					<div class="btn-group btn-group-sm">
						<button class="btn btn-outline-secondary lab-metric-qom-bulk" type="button" value="OK">OK</button>
						<button class="btn btn-outline-secondary lab-metric-qom-bulk" type="button" value="N/A">N/A</button>
						<button class="btn btn-outline-secondary lab-metric-qom-bulk" type="button" value="N/D">N/D</button>
						<button class="btn btn-outline-secondary lab-metric-qom-bulk" type="button" value="N/T">N/T</button>
					</div>
				</div>
				<div>
					<div class="btn-group btn-group-sm">
						<?php
						foreach (\App\UOM::$uom_list as $k => $v) {
							printf('<button class="btn btn-outline-secondary lab-metric-uom-bulk" data-uom="%s" type="button">%s</button>'
								, $k
								, $v
							);
						}
						?>
					</div>
				</div>
			<?php
			}
			?>
		</div>

		<div class="lab-metric-grid" id="lab-metric-type-<?= $lms['id'] ?>">
		<?php
		foreach ($lms['metric_list'] as $lm_id => $lm) {
			switch ($lm['meta']['uom']) {
			case 'bool':
				_draw_metric_select_pass_fail($lm);
				break;
			default:
				_draw_metric($lm);
				break;
			}
		}
		?>
		</div>
	</section>
	<hr>
<?php
}
?>

<div class="form-actions">
	<input name="sample_id" type="hidden" value="<?= $data['Sample']['id'] ?>">
	<button class="btn btn-outline-primary" name="a" value="lab-result-save"><i class="fas fa-save"></i> Save</button>
	<button class="btn btn-outline-danger" name="a" value="lab-result-delete"><i class="fas fa-save"></i> Delete</button>
</div>

</div>
</form>


<script>
$(function() {

	// QOM Handy Picker of All
	$('.lab-metric-qom-bulk').on('click', function() {
		var wrap = $(this).closest('section');
		var sel = this.value;
		if ('OK' == sel) {
			wrap.find('.lab-metric-qom').val('');
		} else {
			wrap.find('.lab-metric-qom').val(sel);
		}
	});

	$('.lab-metric-uom-bulk').on('click', function() {
		debugger;
		var wrap = $(this).closest('section');
		var sel = this.getAttribute('data-uom');
		wrap.find('.lab-metric-uom').val(sel);

	});

	// Attempt Magic on the THC Values?
	var auto_sum = true;
	$('#lab-metric-type-018NY6XC00LMT0HRHFRZGY72C7 input.lab-metric-qom').on('keyup', function() {

		debugger;
		switch (this.id) {
			case 'lab-metric-018NY6XC00V7ACCY94MHYWNWRN':
			case 'lab-metric-018NY6XC00PXG4PH0TXS014VVW':
			case 'lab-metric-018NY6XC00DEEZ41QBXR2E3T97':
			case 'lab-metric-018NY6XC00SAE8Q4JSMF40YSZ3':
				// this.style.borderColor = '#f00';
				// this.setAttribute('data-auto-sum', '0');
				return;
		}

		var sum_all = sum_cbd = sum_thc = 0;
		$('#lab-metric-type-018NY6XC00LMT0HRHFRZGY72C7 input.lab-metric-qom').each(function(i, n) {

			var v = parseFloat(this.value, 10) || 0;
			if (0 == v) {
				return;
			}

			v = (v * 100);

			switch (n.id) {
				case 'lab-metric-018NY6XC00V7ACCY94MHYWNWRN':
				case 'lab-metric-018NY6XC00PXG4PH0TXS014VVW':
				case 'lab-metric-018NY6XC00DEEZ41QBXR2E3T97':
				case 'lab-metric-018NY6XC00SAE8Q4JSMF40YSZ3':
					return; // Ignore These
					break;
				case 'lab-metric-018NY6XC00LM49CV7QP9KM9QH9': // d9-thc
					sum_thc += v;
					break;
				case 'lab-metric-018NY6XC00LMB0JPRM2SF8F9F2': // d9-thca
					sum_thc += (v * 0.877);
					break;
				case 'lab-metric-018NY6XC00LM877GAKMFPK7BMC': // d8-thc ??
					// nothing for now
					break;
				case 'lab-metric-018NY6XC00LMK7KHD3HPW0Y90N': // cbd
					sum_cbd += v;
					break;
				case 'lab-metric-018NY6XC00LMENDHEH2Y32X903': // cbda
					sum_cbd += (v * 0.877);
					break;
			}

			sum_all += v;

		});

		sum_all = sum_all / 100;
		sum_cbd = sum_cbd / 100;
		sum_thc = sum_thc / 100;

		$('#lab-metric-018NY6XC00V7ACCY94MHYWNWRN').val((sum_cbd + sum_thc).toFixed(3));
		$('#lab-metric-018NY6XC00PXG4PH0TXS014VVW').val(sum_thc.toFixed(3));
		$('#lab-metric-018NY6XC00DEEZ41QBXR2E3T97').val(sum_cbd.toFixed(3));
		$('#lab-metric-018NY6XC00SAE8Q4JSMF40YSZ3').val(sum_all.toFixed(3));


	});

	$('.btn-terp-note-auto').on('click', function() {

		var terp_list = [];
		var text_line_list = [];

		$('#lab-metric-type-018NY6XC00LMT07DPNKHQV2GRS input').each(function(i, n) {
			var v = parseFloat(n.value, 10) || 0;
			if (v > 0) {
				terp_list.push({
					node: n,
					value: v,
				});
			}
		});

		// Big on Top
		terp_list.sort(function(a, b) {
			return b.value - a.value;
		});

		var idx = 0;
		var max = Math.min(terp_list.length, 10);

		for (idx=0; idx<max; idx++) {
			var terp = terp_list[idx];
			var node = terp['node'];
			var text = $(node).closest('.input-group').find('.input-group-text').text();
			text_line_list.push(`${text} ${terp.value}`);
		}

		$('#lab-result-terp-note').val(text_line_list.join(', '));

	});

});
</script>


<?php


/**
 *
 */
function _draw_metric($lm)
{
	$uom = $lm['metric']['meta']['uom'] ?: $lm['metric']['uom'];

?>
	<div class="lab-metric-item">
		<div class="input-group">
			<div class="input-group-prepend">
				<div class="input-group-text"><?= __h($lm['name']) ?></div>
			</div>
			<input
				autocomplete="off"
				class="form-control r lab-metric-qom"
				data-auto-sum="1"
				id="<?= sprintf('lab-metric-%s', $lm['id']) ?>"
				name="<?= sprintf('lab-metric-%s', $lm['id']) ?>"
				placeholder="<?= __h($lm['name']) ?>"
				value="<?= $lm['metric']['qom'] ?>">
			<select
				class="form-control lab-metric-uom"
				name="<?= sprintf('lab-metric-%s-uom', $lm['id']) ?>"
				style="flex: 0 1 5em; width: 5em;"
				tabindex="-1">
			<?php
			foreach (\App\UOM::$uom_list as $v => $n) {
				$sel = ($v == $uom ? ' selected' : null);
				printf('<option%s value="%s">%s</option>', $sel, $v, $n);
			}
			?>
			</select>
		</div>
	</div>
<?php
}
