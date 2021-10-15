<?php
/**
 * Show Result List
 */

namespace App\Controller;

class Result extends \App\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$data = array(
			'Page' => array('title' => 'Lab Results'),
			'sync_want' => false,
			'result_list' => array(),
			'result_stat' => [
				'100' => 0,
				'200' => 0,
				'400' => 0,
			]
		);
		$data = $this->loadSiteData($data);


		$sql = <<<SQL
SELECT count(id) AS c, stat
FROM lab_result
 LEFT JOIN lab_result_license ON lab_result.id = lab_result_license.lab_result_id
WHERE lab_result.license_id = :l0
 OR lab_result_license.license_id = :l0
GROUP BY stat
ORDER BY stat
SQL;
		$arg = array(':l0' => $_SESSION['License']['id']);
		$res = $this->_container->DB->fetchAll($sql, $arg);
		foreach ($res as $rec) {
			$data['result_stat'][ $rec['stat'] ] = $rec['c'];
		}
		// _exit_text($data);


		// Stuff my Company is linked to?
		$sql = <<<SQL
SELECT lab_result.*
FROM lab_result
LEFT JOIN lab_result_license ON lab_result.id = lab_result_license.lab_result_id
WHERE lab_result.license_id = :l0 OR lab_result_license.license_id = :l0
ORDER BY created_at DESC, lab_result.id
SQL;

		$arg = array(':l0' => $_SESSION['License']['id']);
		$res = $this->_container->DB->fetchAll($sql, $arg);
		foreach ($res as $rec) {

			$QAR = new \App\Lab_Result($rec);

			$rec['meta'] = \json_decode($rec['meta'], true);

			$rec['coa_file'] = $QAR->getCOAFile();

			// Try to Read first from META -- our preferred data
			$rec['created_at'] = _date('m/d/y', $rec['created_at']);
			$rec['thc'] = $rec['meta']['Result']['thc'] ?: '-';
			$rec['cbd'] = $rec['meta']['Result']['cbd'] ?: '-';
			$rec['sum'] = $rec['meta']['Result']['sum'] ?: '-';
			$rec['testing_status'] = $rec['meta']['Result']['testing_status'];
			$rec['status'] = $rec['meta']['Result']['status'];

			$t = array();
			$x = $rec['meta']['Result']['batch_type'];
			$t[] = $x;

			$x = $rec['meta']['Result']['type'];
			$t[] = $x;

			$x = $rec['meta']['Result']['intermediate_type'];
			$t[] = $x;
			$rec['type'] = trim(implode('/', $t), '/');
			$rec['type_nice'] = $rec['meta']['Product']['type_nice'];
			if (empty($rec['type_nice'])) {
				$rec['type_nice'] = $rec['meta']['Result']['type_nice'];
			}
			if (empty($rec['type_nice'])) {
				$rec['type_nice'] = $rec['type'];
			}

			$stat = array();
			if (!empty($rec['coa_file'])) {
				if (is_file($rec['coa_file'])) {
					$stat[] = ' <i class="far fa-file-pdf"></i>';
				} else {
					$stat[] = ' <i class="far fa-file-pdf text-danger"></i>';
				}
			}

			$x = sprintf('%s/%s', $rec['testing_status'], $rec['status']);
			switch ($x) {
			case 'completed/failed':
				$stat[] = '<i class="fas fa-check-square" style="color: var(--red);"></i>';
				break;
			case 'completed/passed':
				$stat[] = '<i class="fas fa-check-square" style="color: var(--green);"></i>';
				break;
			case 'in_progress/passed':
				$stat[] = '<i class="fas fa-clock"></i> <i class="fas fa-check-square" style="color: var(--green);"></i>';
				break;
			default:
				$stat[] = h($x);
			}

			$rec['status_html'] = implode(' ', $stat);

			$rec['flag_sync'] = ($rec['flag'] & \App\Lab_Result::FLAG_SYNC);
			if (empty($rec['flag_sync'])) {
				$data['sync_want'] = true;
			}

			$data['result_list'][] = $rec;

		}

		return $RES->write( $this->render('result/main.php', $data) );

	}
}
