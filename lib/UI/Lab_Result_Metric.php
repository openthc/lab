<?php
/**
 * Nice Drawing of Lab Result Metric
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\UI;

use OpenTHC\Lab\UOM;

class Lab_Result_Metric
{
	/**
	 *
	 */
	static function input_group($lrm)
	{
		// Special UOM
		switch ($lrm['uom']) {
			case 'bool':
				switch ($lrm['qom']) {
					case 0:
						$lrm['qom'] = 'Fail';
						break;
					case 1:
						$lrm['qom'] = 'Pass';
						break;
				}
				break;
		}

		// Special QOM
		switch ($lrm['qom']) {
			case -1:
				$lrm['qom'] = 'N/A';
				$lrm['uom'] = '';
				break;
			case -2:
				$lrm['qom'] = 'N/D';
				// $lrm['uom'] = '';
				break;
			case -3:
				$lrm['qom'] = 'N/T';
				// $lrm['uom'] = '';
				break;
			case -130:
				$lrm['qom'] = 'T/D';
				// $lrm['uom'] = '';
				break;
		}

		// Status Pass/Fill
		$css = '';
		switch ($lrm['stat']) {
			case 400:
				$css = 'text-danger';
		}

		ob_start();
?>
<div class="result-metric-data" data-metric-id="<?= $lrm['lab_metric_id'] ?>">
<div class="input-group">
	<div class="input-group-text"><?= __h($lrm['name']) ?></div>
	<input class="form-control r <?= $css ?>" readonly style="font-weight: bold;" value="<?= __h($lrm['qom']) ?>">
	<?php
	if ( ! empty($lrm['uom'])) {
		printf('<div class="input-group-text">%s</div>', UOM::nice($lrm['uom']));
	}
	?>
</div>
</div>
<?php
		$html = ob_get_clean();

		return $html;

	}

}
