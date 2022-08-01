<?php
/**
 * Configure Metrics
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller\Config;

use OpenTHC\Lab\Lab_Metric;

class Metric extends \OpenTHC\Lab\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$dbc = $this->_container->DBC_User;

		$data = $this->loadSiteData();
		$data['Page'] = [ 'title' => 'Config :: Metrics' ];

		if ( ! empty($_GET['id'])) {
			return $this->single($RES, $dbc);
		}

		$metric_list = [];
		$sql = <<<SQL
		SELECT lab_metric.id, lab_metric.name, lab_metric.meta
		FROM lab_metric
		JOIN lab_metric_type ON lab_metric.lab_metric_type_id = lab_metric_type.id
		ORDER BY lab_metric_type.sort, lab_metric.sort
		SQL;
		$res = $dbc->fetchAll('SELECT * FROM lab_metric ORDER BY type, sort, name');
		foreach ($res as $m) {
			$m['meta'] = json_decode($m['meta'], true);
			$metric_list[] = $m;
		}
		$data['metric_list'] = $metric_list;

		// Save
		switch ($_POST['a']) {
			case 'lab-metric-mute-toggle':

				switch ($_POST['v']) {
					case 'hide':
						$dbc->query('UPDATE lab_metric SET stat = 410 WHERE id = :lm0', [
							':lm0' => $_POST['lab_metric_id']
						]);
						$html = sprintf('<button class="btn btn-secondary btn-metric-mute-toggle" data-lab-metric-id="%s" type="button" value="show"><i class="fas fa-ban"></i></button>', $_POST['lab_metric_id']);
						__exit_html($html);
						break;
					case 'show':
						$dbc->query('UPDATE lab_metric SET stat = 200 WHERE id = :lm0', [
							':lm0' => $_POST['lab_metric_id']
						]);
						$html = sprintf('<button class="btn btn-success btn-metric-mute-toggle" data-lab-metric-id="%s" type="button" value="hide"><i class="far fa-circle"></i></button>', $_POST['lab_metric_id']);
						__exit_html($html);
						break;
				}
				break;
			case 'save':

				foreach ($data['metric_list'] as $idx => $m) {

					$m['meta']['uom'] = $_POST[sprintf('uom-%s', $m['id'])];
					$m['meta']['lod'] = $_POST[sprintf('lod-%s', $m['id'])];
					$m['meta']['loq'] = $_POST[sprintf('loq-%s', $m['id'])];
					$m['meta']['max'] = $_POST[sprintf('max-%s', $m['id'])];
					$m['meta'] = json_encode($m['meta']);

					$f = $m['flag'];
					$f = ($f & ~ (Lab_Metric::FLAG_FLOWER | Lab_Metric::FLAG_EDIBLE | Lab_Metric::FLAG_EXTRACT));
					if ($_POST[sprintf('bud-%s', $m['id'])]) {
						$f = ($f | Lab_Metric::FLAG_FLOWER);
					}
					if ($_POST[sprintf('edi-%s', $m['id'])]) {
						$f = ($f | Lab_Metric::FLAG_EDIBLE);
					}
					if ($_POST[sprintf('ext-%s', $m['id'])]) {
						$f = ($f | Lab_Metric::FLAG_EXTRACT);
					}
					$m['flag'] = $f;

					$dbc->query('UPDATE lab_metric SET flag = :f1, meta = :m1 WHERE id = :pk', [
						':pk' => $m['id'],
						':f1' => $m['flag'],
						':m1' => $m['meta']
					]);

				}

				return $RES->withRedirect('/config/metric', 303);

			break;
		}

		return $RES->write( $this->render('config/metric.php', $data) );

	}

	/**
	 *
	 */
	function single($RES, $dbc)
	{
		$Lab_Metric = $dbc->fetchRow('SELECT * FROM lab_metric WHERE id = :lm0', [ ':lm0' => $_GET['id'] ]);
		$Lab_Metric['meta'] = json_decode($Lab_Metric['meta'], true);

		$data = $this->loadSiteData();
		$data['Page']['title'] = 'Config :: Metric :: Update';
		$data['Lab_Metric'] = $Lab_Metric;

		return $RES->write( $this->render('config/metric-single.php', $data) );

	}
}
