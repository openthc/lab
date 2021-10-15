<?php
/**
 * Configure Metrics
 */

namespace App\Controller\Config;

use App\Lab_Metric;

class Metric extends \App\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$dbc = $this->_container->DBC_User;

		$data = $this->loadSiteData($data);
		$data['Page'] = [ 'title' => 'Config :: Metrics' ];

		$metric_list = [];
		$res = $dbc->fetchAll('SELECT * FROM lab_metric ORDER BY type, sort, name');
		foreach ($res as $m) {
			$m['meta'] = json_decode($m['meta'], true);
			$metric_list[] = $m;
		}
		$data['metric_list'] = $metric_list;

		// Save
		switch ($_POST['a']) {
			case 'save':

				foreach ($data['metric_list'] as $idx => $m) {

					$m['meta'] = json_decode($m['meta'], true);
					$m['meta']['lod'] = $_POST[sprintf('%s-lod', $m['id'])];
					$m['meta']['loq'] = $_POST[sprintf('%s-loq', $m['id'])];
					$m['meta']['max'] = $_POST[sprintf('%s-max', $m['id'])];
					$m['meta'] = json_encode($m['meta']);

					$f = $m['flag'];
					$f = ($f & ~ (Lab_Metric::FLAG_FLOWER | Lab_Metric::FLAG_EDIBLE | Lab_Metric::FLAG_EXTRACT));
					if ($_POST[sprintf('%s-bud', $m['id'])]) {
						$f = ($f | Lab_Metric::FLAG_FLOWER);
					}
					if ($_POST[sprintf('%s-edi', $m['id'])]) {
						$f = ($f | Lab_Metric::FLAG_EDIBLE);
					}
					if ($_POST[sprintf('%s-ext', $m['id'])]) {
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
}
