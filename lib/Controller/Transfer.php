<?php
/**
 * Search and Import Transfers
 */

namespace App\Controller;

use Edoceo\Radix\DB\SQL;

class Transfer extends \OpenTHC\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		// Load Transfer Stats
		$transfer_stat = [];
		$sql = 'SELECT count(id) AS c, stat FROM transfer_incoming WHERE license_id = :l GROUP BY stat ORDER BY stat';
		$arg = array(':l' => $_SESSION['License']['id']);
		$res = $this->_container->DB->fetchAll($sql, $arg);
		foreach ($res as $rec) {
			$transfer_stat[ $rec['stat'] ] = $rec['c'];
		}

		// Filter
		if (empty($_GET['stat'])) {
			$_GET['stat'] = 200;
		} elseif ('*' == $_GET['stat']) {
			unset($_GET['stat']);
		}

		// Query
		$sql = 'SELECT * FROM transfer_incoming WHERE license_id = :l';
		$arg = array(':l' => $_SESSION['License']['id']);

		if (!empty($_GET['stat'])) {
			$sql .= ' AND stat = :s0';
			$arg[':s0'] = $_GET['stat'];
		}

		$sql.= ' ORDER BY created_at DESC';

		$res = SQL::fetch_all($sql, $arg);
		foreach ($res as $rec) {

			$rec['meta'] = json_decode($rec['meta'], true);
			$rec['date'] = strftime('%m/%d', strtotime($rec['meta']['created_at']));

			$rec['target_license'] = new \OpenTHC\License($rec['license_id']);
			$rec['origin_license'] = new \OpenTHC\License($rec['license_id_source']);

			$transfer_list[] = $rec;
		}

		$data = array(
			'Page' => array('title' => 'Transfers'),
			'transfer_stat' => $transfer_stat,
			'transfer_list' => $transfer_list,
		);

		return $RES->write( $this->render('page/transfer/index.html', $data) );

	}

}
