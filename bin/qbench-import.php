#!/usr/bin/php
<?php
/**
 * QBench Data Importer
 *
 * SPDX-License-Identifier: GPL-3.0-only
 *
 * @see https://junctionconcepts.zendesk.com/hc/en-us/articles/360030760992-QBench-REST-API-v1-0-Documentation-Full-
 *
 */

use Edoceo\Radix\DB\SQL;

use OpenTHC\Product;
use OpenTHC\Product_Type;
use OpenTHC\Variety;

use OpenTHC\CRE\Base as CRE_Base;

use OpenTHC\Lab\Lab_Result;
use OpenTHC\Lab\Lab_Result_Metric;
use OpenTHC\Lab\Lab_Sample;

require_once(dirname(dirname(__FILE__)) . '/boot.php');
require_once(APP_ROOT . '/vendor/openthc/cre-adapter/lib/QBench.php');

openlog('openthc-lab', LOG_ODELAY | LOG_PERROR | LOG_PID, LOG_LOCAL0);

define('LAB_METRIC_SKIP', '018NY6XC00LM00000000000000');

// Company ID from Arg?
$opt = _cli_options();

$dbc_auth = _dbc('auth');

$dsn = $dbc_auth->fetchOne('SELECT dsn FROM auth_company WHERE id = :c0', [ ':c0' => $opt['--company'] ]);
$dbc = new SQL($dsn);

$_SESSION['Company'] = $dbc->fetchRow('SELECT * FROM auth_company WHERE id = :c0', [ ':c0' => $opt['--company'] ]);
$_SESSION['License'] = $dbc->fetchRow('SELECT * FROM license WHERE id = :l0', [ ':l0' => $opt['--license'] ]);
if (empty($_SESSION['Company']['id'])) {
	echo "Invalid Company\n";
	exit(1);
}
if (empty($_SESSION['License']['id'])) {
	echo "Invalid License\n";
	exit(1);
}

$cfg = $dbc->fetchOne("SELECT val FROM base_option WHERE key = 'qbench-auth'");
if (empty($cfg)) {
	echo "Invalid QBench Configuration\n";
	exit(1);
}
$cfg = json_decode($cfg, true);

$qbc = new \OpenTHC\CRE\QBench($cfg);

$res = $qbc->auth();

// List of possible panels
// $lab_panel_list = [];
// $res = $qbc->get('/api/v1/panel');
// foreach ($res['data'] as $x) {
// 	$x['@id'] = sprintf('qbench:%d', $x['id']);
// 	$lab_panel_list[$x['@id']] = $x;
// 	// printf("Panel: %s / '%s'\n", $x['@id'], $x['title']);
// }

// Assay List
$lab_assay_list = [];
$res = $qbc->get('/api/v1/assay');
foreach ($res['data'] as $x) {
	$x['@id'] = sprintf('qbench:%d', $x['id']);
	$lab_assay_list[$x['@id']] = $x;
}

// Accessioning Type
// $lab_accession_list = [];
// $res = $qbc->get('/api/v1/accessioningtype');
// foreach ($res['data'] as $x) {
// 	$x['@id'] = sprintf('qbench:%d', $x['id']);
// 	// var_dump($x);
// }

$dts = new DateTime();

// _qbench_pull_report($dbc, $qbc);
if (in_array('license', $opt['--object'])) {
	_qbench_pull_license($dbc, $qbc);
	$dbc->query('INSERT INTO base_option (key, val) VALUES (:k1, :v1) ON CONFLICT (key) DO UPDATE SET val = :v1', [
		':k1' => 'sync/license/timestamp',
		':v1' => json_encode($dts->format(\DateTimeInterface::RFC3339))
	]);
}
if (in_array('contact', $opt['--object'])) {
	_qbench_pull_contact($dbc, $qbc);
	$dbc->query('INSERT INTO base_option (key, val) VALUES (:k1, :v1) ON CONFLICT (key) DO UPDATE SET val = :v1', [
		':k1' => 'sync/contact/timestamp',
		':v1' => json_encode($dts->format(\DateTimeInterface::RFC3339))
	]);
}
if (in_array('b2b', $opt['--object'])) {
	_qbench_b2b_import($dbc, $qbc);
	$dbc->query('INSERT INTO base_option (key, val) VALUES (:k1, :v1) ON CONFLICT (key) DO UPDATE SET val = :v1', [
		':k1' => 'sync/b2b/timestamp',
		':v1' => json_encode($dts->format(\DateTimeInterface::RFC3339))
	]);
}
if (in_array('sample', $opt['--object'])) {
	_qbench_pull_sample($dbc, $qbc);
	$dbc->query('INSERT INTO base_option (key, val) VALUES (:k1, :v1) ON CONFLICT (key) DO UPDATE SET val = :v1', [
		':k1' => 'sync/lab_sample/timestamp',
		':v1' => json_encode($dts->format(\DateTimeInterface::RFC3339))
	]);
}
if (in_array('result', $opt['--object'])) {
	_qbench_pull_result($dbc, $qbc);
	$dbc->query('INSERT INTO base_option (key, val) VALUES (:k1, :v1) ON CONFLICT (key) DO UPDATE SET val = :v1', [
		':k1' => 'sync/lab_result/timestamp',
		':v1' => json_encode($dts->format(\DateTimeInterface::RFC3339))
	]);
}

// A Sample Report Contains a Data-Set of Which Test Result objects are included in the REPORT
// A REPORT is a collection of 1=Sample & 1+Result Object each with 1+Metric Objects
// $res = $qbc->get('/api/v1/report/sample/33535/info');
// var_dump($res);

// Get the Tests (an actual Test)

// foreach ($res['data'] as $rec) {

// 	echo "Assay: {$rec['id']} {$rec['title']}\n";

// 	$d = json_encode($rec);
// 	$f = sprintf('%s/tmp/qbench/a%s.json', APP_ROOT, md5($d));
// 	file_put_contents($f, $d);

// Get Contact/License Data from QBench
function _qbench_pull_license($dbc, $qbc)
{
	$dt0 = new DateTime('2000-01-01');
	$chk = $dbc->fetchOne("SELECT val FROM base_option WHERE key = 'sync/license/timestamp'");
	$chk = json_decode($chk, true);
	if ( ! empty($chk)) {
		$dt0 = new DateTime($chk);
		$dt0->sub(new DateInterval('P30D'));
	}

	printf("_qbench_pull_license(%s)\n", $dt0->format(\DateTimeInterface::RFC3339));

	$hit = 0;
	$idx = 1;
	$max = $idx;

	do {

		$res = $qbc->get('/api/v1/customer?' . http_build_query([
			'page_num' => $idx,
			'sort_by' => 'last_updated',
			'sort_order' => 'desc',
		]));
		$max = intval($res['total_pages']);

		foreach ($res['data'] as $rec) {

			// Compare to Sync Time
			$dt1 = new DateTime(sprintf('@%d', $rec['last_updated']));
			if ($dt1 < $dt0) {
				echo "TIMEOUT\n";
				return(0);
			}

			// $obj = \OpenTHC\CRE\QBench\License::convert($rec);

			$rec['@id'] = sprintf('qbench:%s', $rec['id']);
			$rec['customer_name'] = trim($rec['customer_name']);
			$rec['license_number'] = trim($rec['license_number']);
			if (preg_match('/^\d{6}$/', $rec['license_number'])) {
				$rec['@code'] = $rec['license_number'];
			}
			if (empty($rec['@code'])) {
				$rec['@code'] = $rec['@id'];
			}
			$rec['@hash'] = CRE_Base::recHash($rec);

			// guid1 and guid0 may be needed here
			$lic0 = $dbc->fetchRow('SELECT id, code, hash, name FROM license WHERE guid = :g0', [
				':g0' => $rec['@id']
			]);
			if ( ! empty($lic0['id']) && ($rec['@hash'] == $lic0['hash'])) {
				$hit++;
				syslog(LOG_NOTICE, "License {$lic0['id']} STABLE");
				continue;
			}

			if (empty($lic0['id'])) {

				// License = customer_account_id?
				// License = explode(',', strtolower($rec['email_to']));
				$lic0 = [
					'id' => _ulid()
					, 'guid' => $rec['@id']
					, 'code' => $rec['@code']
					, 'name' => $rec['customer_name']
					// , 'address_full' => trim($rec['address'])
					// , 'email' => trim($rec['email_address'])
					// , 'phone' => trim($rec['phone'])
					, 'hash' => $rec['@hash']
					, 'meta' => json_encode($rec)
					, 'type' => 'Grower'
				];

				$dbc->insert('license', $lic0);

				syslog(LOG_NOTICE, "License {$lic0['id']} CREATE");

			} else {

				$update = [];
				$update['code'] = $rec['@code'];
				$update['hash'] = $rec['@hash'];
				$update['name'] = $rec['customer_name'];
				$update['meta'] = json_encode($rec);
				$update['updated_at'] = date(\DateTime::RFC3339, $rec['last_updated']);

				$filter = [];
				$filter['id'] = $lic0['id'];
				$dbc->update('license', $update, $filter);

				syslog(LOG_NOTICE, "License {$lic0['id']} UPDATE");

			}

		}

		$idx++;

		echo "\n";

	} while ($idx <= $max);

}

// Get Contact/License Data from QBench
function _qbench_pull_contact($dbc, $qbc)
{
	$dt0 = new DateTime('2000-01-01');
	$chk = $dbc->fetchOne("SELECT val FROM base_option WHERE key = 'sync/contact/timestamp'");
	$chk = json_decode($chk, true);
	if ( ! empty($chk)) {
		$dt0 = new DateTime($chk);
		$dt0->sub(new DateInterval('P7D'));
	}

	printf("_qbench_pull_contact(%s)\n", $dt0->format(\DateTimeInterface::RFC3339));

	$idx = 1;
	$max = $idx;

	do {

		$res = $qbc->get('/api/v1/contact?' . http_build_query([
			'page_num' => $idx,
			'sort_by' => 'last_updated',
			'sort_order' => 'desc',

		]));
		$max = intval($res['total_pages']);

		echo "Page: $idx/$max";

		foreach ($res['data'] as $rec) {

			// Compare to Sync Time
			$dt1 = new DateTime(sprintf('@%d', $rec['last_updated']));
			if ($dt1 < $dt0) {
				echo "TIMEOUT\n";
				return(0);
			}

			$rec['@id'] = sprintf('qbench:%s', $rec['id']);

			// What Am I trying to do here? /djb 2024-07-31
			if ( ! empty($rec['email'])) {
				var_dump($rec);
				throw new \Exception('Invalid Contact, Missing Email');
			}

			// Pending Record?
			$ct0 = [
				'id' => _ulid()
				, 'guid' => $rec['@id']
				, 'meta' => json_encode($rec)
				, 'type' => 'X'
				, 'email' => trim(strtolower($rec['email'] ?: $rec['email_address']))
				, 'phone' => ($rec['mobile'] ?: $rec['phone'])
				, 'fullname' => trim(sprintf('%s %s', $rec['first_name'], $rec['last_name']))
			];
			$ct0 = array_filter($ct0);
			$ct0['hash'] = sha1($ct0['meta']);

			// echo "Contact: {$ct0['guid']} {$ct0['email']} ";

			$chk = $dbc->fetchRow('SELECT id FROM contact WHERE guid = :g0', [
				':g0' => $rec['@id']
			]);

			if (empty($chk['id'])) {

				echo '+';

				try {
					$dbc->insert('contact', $ct0);
				} catch (\Exception $e) {
					echo $e->getMessage();
					var_dump($rec);
					exit;
				}

			} else {
				echo '=';
			}

		}

		$idx++;

		echo "\n";

	} while ($idx <= $max);

}


/**
 *
 */
function _qbench_pull_result($dbc, $qbc)
{
	// Fetch Specific One
	if ( ! empty($_ENV['object-id'])) {

		$oid = $_ENV['object-id'];

		// Update Object Hash in Database to '-' so it will re-pull
		$dbc->query("UPDATE lab_result SET hash = 'SYNC' WHERE id = :lr0", [ ':lr0' => $oid ]);
		// DELETE lab_result_metric for this lab_result so it's clean for import
		$dbc->query("DELETE FROM lab_result_metric WHERE lab_result_id = :lr0", [ ':lr0' => $oid ]);

		$oid = str_replace('qbench:', '', $oid);
		$rec = $qbc->get(sprintf('/api/v1/test/%s', $oid));

		try {
			$x = _qbench_pull_result_import($dbc, $rec);
		} catch (\Exception $e) {
			echo "Failed to import Result: $oid\n";
			echo $e->getMessage();
			echo "\n";
		}

		return $x;
	}


	$dt0 = new DateTime('2023-03-01');
	// $chk = $dbc->fetchOne("SELECT val FROM base_option WHERE key = 'sync/lab_result/timestamp'");
	// $chk = json_decode($chk, true);
	// if ( ! empty($chk)) {
	// 	$dt0 = new DateTime($chk);
	// 	$dt0->sub(new DateInterval('P21D'));
	// }

	printf("_qbench_pull_lab_result(%s)\n", $dt0->format(\DateTimeInterface::RFC3339));

	$hit = 0;
	$idx = 1;
	$max = $idx;

	do {

		$res = $qbc->get('/api/v1/test?' . http_build_query([
			'page_num' => $idx,
			'sort_by' => 'last_updated', // 'id',
			'sort_order' => 'desc'
		]));

		$max = intval($res['total_pages']);

		foreach ($res['data'] as $rec) {

			// Compare to Sync Time
			$dt1 = new DateTime(sprintf('@%d', $rec['last_updated']));
			if ($dt1 < $dt0) {
				echo "TIMEOUT\n";
				return(0);
			}

			try {
				$x = _qbench_pull_result_import($dbc, $rec);
				$hit += $x;
			} catch (\Exception $e) {
				echo $e->getMessage();
				echo "\n";
				echo "Failed to Import Result: {$rec['id']} from Sample {$rec['sample_id']}\n";
			}

		}

		$idx++;

		echo "\nwhile (($idx <= $max) && ($hit < 1000));\n";

	} while (($idx <= $max) && ($hit < 1000));

}

/**
 * Import a Result and save Lab Result Metric
 */
function _qbench_pull_result_import($dbc, $rec) : int
{
	global $lab_assay_list;
	// $lab_assay_list = $qbc->getAssayList();

	$rec['@id'] = sprintf('qbench:%s', $rec['id']);
	$rec['@assay_id'] = sprintf('qbench:%s', $rec['assay_id']);

	if ( ! empty($rec['panel_id'])) {
		$rec['@panel_id'] = sprintf('qbench:%s', $rec['panel_id']);
	}

	$rec['@lab_result_id'] = sprintf('qbench:%s', $rec['id']);
	$rec['@lab_sample_id'] = sprintf('qbench:%s', $rec['sample_id']);

	// State Mapper
	switch (strtoupper($rec['state'])) {
		case 'NOT STARTED':
			$rec['@stat'] = Lab_Result::STAT_OPEN;
			break;
		case 'BEING TESTED':
		case 'NEEDS GC DATA':
		case 'NEEDS LC DATA':
		case 'IN DATA REVIEW':
		case 'WAITING ON MORE SAMPLE':
			$rec['@stat'] = Lab_Result::STAT_WAIT;
			break;
		case 'RETEST - CONFIRMATION':
		case 'RETEST - DILUTION REQUIRED':
		case 'RETEST - REPREP REQUIRED':
			$rec['@stat'] = 307;
			break;
		case 'CANCELLED':
			$rec['@stat'] = Lab_Result::STAT_VOID;
			break;
		case 'SUBCONTRACTING':
			$rec['@stat'] = 307;
			break;
		case 'COMPLETED':
			$rec['@stat'] = Lab_Result::STAT_PASS;
			break;
		default:
			var_dump($rec);
			throw new \Exception('Invalid Status [IQB-313]');
	}

	$rec['@hash'] = CRE_Base::recHash($rec);

	$dbc->query('BEGIN');

	$lr0 = $dbc->fetchRow('SELECT id, hash, name, stat FROM lab_result WHERE (id = :g1 OR guid = :g1)', [
		':g1' => $rec['@lab_result_id']
	]);
	if ( ! empty($lr0['id']) && ($lr0['hash'] == $rec['@hash'])) {
		syslog(LOG_NOTICE, "Lab Result {$lr0['id']} STABLE");
		return(1);
	}

	$dtA = $dtE = null;
	if ( ! empty($rec['complete_date'])) {

		$dtA = new \DateTime($rec['complete_date']);
		$dtE = clone $dtA;
		$dtE->add(new \DateInterval('P365D'));

	}

	// Lab Sample?
	$ls0 = $dbc->fetchRow('SELECT id, lot_id FROM lab_sample WHERE id = :g1', [ // v0 lot_id
		':g1' => $rec['@lab_sample_id']
	]);
	if (empty ($ls0['id'])) {
		throw new \Exception(sprintf('Invalid Sample "%s" on Result "%s" [BIQ-483]', $rec['@lab_sample_id'], $rec['@id']));
	}
	// Inventory Data
	$inv = $dbc->fetchRow('SELECT id, guid FROM inventory WHERE id = :g1', [
		':g1' => $ls0['lot_id'] // v0 lot_id
	]);
	if (empty($inv['id'])) {
		var_dump($rec);
		throw new \Exception('Invalid Inventory on Result');
		$inv = [
			'id' => $ls0['lot_id'], // v0 lot_id
			'guid' => $ls0['lot_id'], // v0 lot_id
			'name' => sprintf('%s - IMPORT-527', $ls0['lot_id']), // v0 lot_id
			'license_id' => $_SESSION['License']['id'],
			'product_id' => '018NY6XC00PR0DUCT000000000', // $rec['sample_name'] sometimes?
			'variety_id' => '018NY6XC00VAR1ETY000000000', // $rec['sample_name'] sometimes?
			'section_id' => '018NY6XC00SECT10N000000000',
			'stat' => 100,
		];

		$dbc->insert('inventory', $inv);
		// 'license_id_source' => $b2b['license_id_source']
		// 'qty' => $rec['@qty'],
		// 'qty_initial' => $rec['@qty'],
		// 'stat' => 200
	}

	if (empty($lr0['id'])) {

		$lr1 = [
			'id' => sprintf('qbench:%s', $rec['id'])
			, 'guid' => sprintf('qbench:%s', $rec['id'])
			, 'license_id' => $_SESSION['License']['id']
			, 'lab_sample_id' => $rec['@lab_sample_id']
			// , 'inventory_id' => '018NY6XC00L0T0000000000000'
			, 'hash' => $rec['@hash']
			, 'name' => ( $lab_assay_list[ $rec['@assay_id'] ]['title'] ?: sprintf('QBench Result %s', $rec['id']) )
			, 'stat' => $rec['@stat']
			, 'uom' => 'g'
			, 'created_at' => $rec['date_created'] // No time component
		];
		if ( ! empty($dtA)) {
			$lr1['approved_at'] = $dtA->format(\DateTime::RFC3339);
			$lr1['expires_at'] = $dtE->format(\DateTime::RFC3339);
		}

		$dbc->insert('lab_result', $lr1);
		$lr0 = new Lab_Result($dbc, $lr1);

		syslog(LOG_NOTICE, "Lab Result {$lr0['id']} CREATE");

	} else {

		$lr0 = new Lab_Result($dbc, $lr0);
		$lr0['hash'] = $rec['@hash'];
		$lr0['name'] = ( $lab_assay_list[ $rec['@assay_id'] ]['title'] ?: sprintf('QBench Result %s', $rec['id']) );
		$lr0['stat'] = $rec['@stat'];
		if ( ! empty($dtA)) {
			$lr0['approved_at'] = $dtA->format(\DateTime::RFC3339);
			$lr0['expires_at'] = $dtE->format(\DateTime::RFC3339);
		}
		$lr0['meta'] = json_encode($rec);
		$lr0->save('Lab/Result Updated via Import');

		syslog(LOG_NOTICE, "Lab Result {$lr0['id']} UPDATE");

	}

	// QBench puts lab_result_metric data into a worksheet
	if ( ! empty($rec['worksheet_data'])) {

		foreach ($rec['worksheet_data'] as $metric_key => $metric_val) {

			$metric_val['@key'] = $metric_key;

			// Map Key to ULID
			// However, the QBench Data has a Preference for Authortative Data in mg/L field.
			// And then stuff in mg/g and then mg/mL then % then mg/unit
			// So, if there is an ND in the ${NAME}_mg_l field then use that, not the ZERO value in %
			$lm0 = _qbench_map_metric($dbc, $metric_key);
			$lm0['meta'] = json_decode($lm0['meta'], true);

			switch ($lm0['id']) {
			case LAB_METRIC_SKIP:
				// echo "Lab/Metric/Ignore: $metric_key\n";
				continue 2;
				break;
			}

			// Patch the Metric Values to Real Shit
			$val = $metric_val['value'];
			if ( ! is_numeric($val)) {
				// Sadly, these can be basically anything
				// And are defined by each user of QBench
				switch (strtoupper($val)) {
					case 'DET': // Detected
						$val = -130;
						break;
					case 'FAIL':
						$val = -1;
						break;
					case 'ND':
						$val = -2;
						break;
					case 'NT':
						$val = -134; // v0
						$val = -3; // v1
						break;
					case 'PASS':
						$val = 1;
						// $val = -587;
						// $uom = bool;
						break;
					case 'TNTC': // Too Numerous to Count
						$val = -138;
						break;
					case 'TRACE':
						$val = -137;
						break;
					// case '33,333':
					// case '>20':
					// case '>1%':
					case '0..1':
						$val = 0.1;
						break;
					case 'CFM':
						$val = 0;
						break;
					default:
						if ('>' == substr($val, 0, 1)) {
							// case '>10000': // GT-LOQ
							// case '>20,000': // GT-LOQ
							// case '>20': // GT-LOQ
							// case '>1%':
							$val = -416;
						} else {
							$dbc->query('ROLLBACK');
							var_dump($rec['@lab_result_id']);
							var_dump($rec['@lab_sample_id']);
							var_dump($metric_val);
							// throw new \Exception(sprintf('Invalid Value: "%s"', $val));
						}
						echo "Invalid Value: {$metric_val['value']}\n";

				}
			}
			$val = floatval($val);

			$lrm0 = $dbc->fetchRow('SELECT id FROM lab_result_metric WHERE lab_result_id = :lr0 AND lab_metric_id = :lm0', [
				':lr0' => $lr0['id']
				, ':lm0' => $lm0['id'],
			]);

			$lrm1 = [
				'id' => _ulid()
				, 'stat' => Lab_Result_Metric::STAT_PASS
				, 'lab_result_id' => $lr0['id']
				, 'lab_metric_id' => $lm0['id']
				, 'qom' => $val
				, 'uom' => $lm0['uom']
			];

			// Check Action Limit
			// $Lab_Result_Metric->isPassed(); ?
			if ( ! empty($lm0['meta']['max']['val'])) {
				$al0 = floatval($lm0['meta']['max']['val']);
				if ($val >= $al0) {
					$lrm1['stat'] = Lab_Result_Metric::STAT_FAIL;
					$lr0['stat'] = Lab_Result::STAT_FAIL;
				}
			}

			if (empty($lrm0['id'])) {

				try {
					$dbc->insert('lab_result_metric', $lrm1);
				} catch (\Exception $e) {
					echo "\n!!!!\n";
					echo $e->getMessage();
					var_dump($lrm1);
					echo "\n####\n";
					// exit(0);
					continue;
				}

			} else {
				$update = [];
				$update['stat'] = $lrm1['stat'];
				$update['qom'] = $lrm1['qom'];
				$update['uom'] = $lrm1['uom'];
				$filter = [];
				$filter['id'] = $lrm0['id'];
				$dbc->update('lab_result_metric', $update, $filter);
			}

		}

	}

	//If Assay is Cannabinoids?
	// 018NY6XC00SAE8Q4JSMF40YSZ3
	switch ($rec['@assay_id']) {
		case 'qbench:1':  // Cannabinoid-Standard
		case 'qbench:9':  // Expanded Cannabinoid (17 compounds-extra $)
		case 'qbench:21': // 'Retest-Cannabinoid'
		case 'qbench:30': // Reporting Cannabinoid
			$lr0->updateCannabinoids();
			break;
	}

	// Update Lab Sample to point use result-status
	// $dbc->query('UPDATE lab_sample SET stat = :s1 WHERE id = :ls0 AND stat != :s1', [
	// 	':ls0' => $rec['@lab_sample_id'],
	// 	':s1' => $lr0['stat'],
	// ]);

	$lr0->save('Lab_Result/Import Update');
	$dbc->query('COMMIT');

	return 0;

}


/**
 *
 */
function _qbench_pull_sample($dbc, $qbc)
{
	// Fetch Specific One
	if ( ! empty($_ENV['object-id'])) {

		$oid = $_ENV['object-id'];

		// Update Object Hash in Database to '-' so it will re-pull
		$dbc->query("UPDATE lab_sample SET hash = 'SYNC' WHERE id = :ls0", [ ':ls0' => $oid ]);

		$oid = str_replace('qbench:', '', $oid);
		$rec = $qbc->get(sprintf('/api/v1/sample/%s', $oid));

		try {
			$x = _qbench_pull_sample_import($dbc, $rec);
		} catch (\Exception $e) {
			echo "Failed to import Sample: $oid\n";
			echo $e->getMessage();
			echo "\n";
		}

		return $x;

	}

	$dt0 = new DateTime('2000-01-01');
	$chk = $dbc->fetchOne("SELECT val FROM base_option WHERE key = 'sync/lab_sample/timestamp'");
	$chk = json_decode($chk, true);
	if ( ! empty($chk)) {
		$dt0 = new DateTime($chk);
		$dt0->sub(new DateInterval('P21D'));
	}

	printf("_qbench_pull_sample(%s)\n", $dt0->format(\DateTimeInterface::RFC3339));

	$hit = 0;
	$idx = 1;
	$max = $idx;

	do {

		$res = $qbc->get('/api/v1/sample?' . http_build_query([
			'page_num' => $idx
			, 'sort_by' => 'last_updated' // , // 'id'
			, 'sort_order' => 'desc'
		]));

		$max = intval($res['total_pages']);

		foreach ($res['data'] as $rec) {

			// Compare to Sync Time
			$dt1 = new DateTime(sprintf('@%d', $rec['last_updated']));
			if ($dt1 < $dt0) {
				echo "TIMEOUT\n";
				return(0);
			}

			$x = _qbench_pull_sample_import($dbc, $rec);
			$hit += $x;

		}

		$idx++;

		echo "\nwhile (($idx < $max) && ($hit < 1000));\n";

	} while (($idx < $max) && ($hit < 1000));

}

/**
 *
 */
function _qbench_pull_sample_import($dbc, $rec)
{

	if (empty($rec['order_id'])) {
		throw new \Exception(sprintf('Missing Order ID on Sample "%s" [BIQ-807]', $rec['@id']));
	}

	$rec['@id'] = sprintf('qbench:%s', $rec['id']);
	$rec['@inventory_guid'] = $rec['ExtInvID'] ?: $rec['lot_number'] ?: $rec['custom_formatted_id'] ?: $rec['@id'];
	$rec['@order_id'] = sprintf('qbench:%s', $rec['order_id']);
	$rec['@qty'] = floatval($rec['sample_quantity_received']);
	if (preg_match('/([0-9\.]+)(a-z)/i', $rec['sample_quantity_received'], $m)) {
		$rec['@qty'] = $m[1];
		$rec['@uom'] = $m[2];
	}
	$key_list = array_keys($rec);
	foreach ($key_list as $k) {
		if (is_string($rec[$k])) {
			$rec[$k] = trim($rec[$k]);
		}
	}
	$rec['@hash'] = CRE_Base::recHash($rec);

	syslog(LOG_NOTICE, "Lab Sample: {$rec['@id']} / {$rec['@inventory_guid']} / {$rec['custom_formatted_id']}");

	// Lookup Record
	$lab_sample = $dbc->fetchRow('SELECT id, hash FROM lab_sample WHERE id = :i0 OR name = :i0', [
		':i0' => $rec['@id']
	]);

	if ( ! empty($lab_sample['id']) && ($rec['@hash'] == $lab_sample['hash'])) {
		return 1;
	}

	$b2b = $dbc->fetchRow('SELECT id, license_id_source, meta FROM b2b_incoming WHERE guid = :g0', [
		':g0' => $rec['@order_id']
	]);
	if (empty($b2b)) {
		print_r($rec);
		syslog(LOG_NOTICE, "Lab Sample: {$rec['@id']} Missing Order '{$rec['@order_id']}'");
		return 0;
	}

	// Need to Create an Inventory Lot Here
	$inv = $dbc->fetchRow('SELECT id, guid, name FROM inventory WHERE guid = :g0', [ ':g0' => $rec['@inventory_guid'] ]);
	if (empty($inv['id'])) {
		syslog(LOG_NOTICE, "Lab Sample: {$rec['@id']} Inventory {$rec['@inventory_guid']} CREATE");
		$inv = [
			'id' => _ulid(), // $rec['@id'],
			'guid' => $rec['@inventory_guid'],
			'name' => sprintf('%s - IMPORT-875', $rec['@inventory_guid']),
			'license_id' => $_SESSION['License']['id'],
			// 'license_id_source' => $b2b['license_id_source']
			'product_id' => '018NY6XC00PR0DUCT000000000', // $rec['sample_name'] sometimes?
			'variety_id' => '018NY6XC00VAR1ETY000000000', // $rec['sample_name'] sometimes?
			'section_id' => '018NY6XC00SECT10N000000000',
			'qty' => $rec['@qty'],
			'qty_initial' => $rec['@qty'],
			'stat' => 200
		];
		$dbc->insert('inventory', $inv);

	}

	// Link it to a Lab_Sample
	if (empty($lab_sample)) {

		$lab_sample = [];
		$lab_sample['id'] = $rec['@id'];
		$lab_sample['stat'] = Lab_Sample::STAT_OPEN; // checkout received(bool) also
		$lab_sample['hash'] = $rec['@hash'];
		$lab_sample['name'] = $rec['custom_formatted_id'] ?: $rec['sample_name'];
		$lab_sample['created_at'] = $rec['date_created'];
		$lab_sample['updated_at'] = date(\DateTime::RFC3339, $rec['last_updated']);
		$lab_sample['license_id'] = $_SESSION['License']['id'];
		$lab_sample['license_id_source'] = $b2b['license_id_source'];
		$lab_sample['lot_id'] = $inv['id']; // v0 lot_id
		// $lab_sample['inventory_id'] = $inv['id']; // v1 inventory_id
		$lab_sample['qty'] = $rec['@qty'];
		$lab_sample['meta'] = json_encode($rec);

		$dbc->insert('lab_sample', $lab_sample);
		syslog(LOG_NOTICE, "Lab Sample: {$rec['@id']} CREATE");

	} else {

		// $lab_sample['stat'] = // res['status'] == "IN PROGRESS", "IN REVIEW", NULL
		$update = [];
		$update['hash'] = $rec['@hash'];
		$update['meta'] = json_encode($rec);
		$update['lot_id'] = $inv['id'];
		$update['updated_at'] = date(\DateTime::RFC3339, $rec['last_updated']);

		switch ($rec['status']) {
			case '':
				$update['stat'] = Lab_Sample::STAT_OPEN;
				break;
			case 'IN PROGRESS':
				// Should PROC == 102?
				$update['stat'] = Lab_Sample::STAT_LIVE;
				break;
			case 'IN REVIEW':
				$update['stat'] = Lab_Result::STAT_WAIT;
				break;
			default:
				var_dump($rec);
				throw new \Exception("Bad Sample Status '{$rec['status']}' ");
		}

		$filter = [];
		$filter['id'] = $rec['@id'];

		try {
			$dbc->update('lab_sample', $update, $filter);
			syslog(LOG_NOTICE, "Lab Sample: {$rec['@id']} UPDATE");
		} catch (Exception $e) {
			// Sometimes this fails because of duplicated GUID values which we don't allow.
			// Would need to move one to a -0 and then update the new one to be -1
			echo "\nSample: {$rec['@id']}\n";
			echo $e->getMessage();
			echo "\n";

		}

		// $hit++;

	}

	// Link it to the B2B Incoming Item
	$b2b_item = $dbc->fetchRow('SELECT id, lot_id FROM b2b_incoming_item WHERE b2b_incoming_id = :b0 AND id = :b1', [
		':b0' => $b2b['id']
		, ':b1' => $rec['@id']
	]);
	if (empty($b2b_item)) {

		// Insert B2B Record
		$dbc->insert('b2b_incoming_item', [
			'id' => $rec['@id']
			, 'b2b_incoming_id' => $b2b['id']
			, 'lot_id' => $inv['id'] // v0 lot_id
			, 'created_at' => $rec['date_created']
			, 'updated_at' => date(\DateTime::RFC3339, $rec['last_updated'])
			// , 'deleted_at' =>
			, 'stat' => 200
			, 'flag' => 0
			, 'unit_count' => $rec['@qty']
			, 'hash' => '-'
			, 'name' => $rec['@id']
			, 'meta' => json_encode($rec)
		]);
	} elseif (empty($b2b_item['lot_id'])) {
		// Update?
		$update = [];
		$update['lot_id'] = $inv['id'];
		$filter = [];
		$filter['id'] = $b2b_item['id'];
		$dbc->update('b2b_incoming_item', $update, $filter);
		// echo '=B';
	}

	return 0;

}

// Get Orders
function _qbench_b2b_import($dbc, $qbc)
{
	// 6531
	// Fetch Specific One
	if ( ! empty($_ENV['object-id'])) {

		$oid = $_ENV['object-id'];

		// Update Object Hash in Database to '-' so it will re-pull
		$dbc->query("UPDATE b2b_incoming SET hash = 'SYNC' WHERE id = :o0", [ ':o0' => $oid ]);

		$oid = str_replace('qbench:', '', $oid);
		$rec = $qbc->get(sprintf('/api/v1/order/%s', $oid));

		try {
			$x = _qbench_sync_b2b_incoming_import($dbc, $rec);
		} catch (\Exception $e) {
			echo "Failed to import B2B Incoming: $oid\n";
			echo $e->getMessage();
			echo "\n";
		}

		return $x;

	}


	$dt0 = new DateTime('2000-01-01');
	$chk = $dbc->fetchOne("SELECT val FROM base_option WHERE key = 'sync/b2b/timestamp'");
	$chk = json_decode($chk, true);
	if ( ! empty($chk)) {
		$dt0 = new DateTime($chk);
		$dt0->sub(new DateInterval('P14D'));
	}

	printf("_qbench_b2b_import(%s)\n", $dt0->format(\DateTimeInterface::RFC3339));

	$hit = 0;
	$idx = 1;
	$max = $idx;

	do {

		$res = $qbc->get('/api/v1/order?' . http_build_query([
			'page_num' => $idx
			, 'sort_by' => 'last_updated'
			, 'sort_order' => 'desc'
		]));

		$max = intval($res['total_pages']);

		foreach ($res['data'] as $rec) {

			// Compare to Sync Time
			$dt1 = new DateTime(sprintf('@%d', $rec['last_updated']));
			if ($dt1 < $dt0) {
				$x = $dt1->format(\DateTimeInterface::RFC3339);
				syslog(LOG_INFO, "B2B/Incoming sync timeout; $x");
				return(0);
			}

			_qbench_sync_b2b_incoming_import($dbc, $rec);

		}

		$idx++;

	} while ($idx <= $max);

}

/**
 *
 */
function _qbench_sync_b2b_incoming_import($dbc, $rec) : int
{
	$rec['@id'] = sprintf('qbench:%s', $rec['id']);
	$rec['@hash'] = CRE_Base::recHash($rec);

	// echo "b2b: {$rec['@id']}\n";
	$chk = $dbc->fetchRow('SELECT id, hash, stat, license_id_source FROM b2b_incoming WHERE guid = :x1', [
		':x1' => $rec['@id']
	]);

	if ( ! empty($rec['WCIA_JSON_OrderInput'])) {
		$rec['@source'] = _qbench_b2b_import_wcia($rec['WCIA_JSON_OrderInput']);
		// clear hash to reload
		$chk['hash'] = null;
	}

	// Hash Match
	if ( ! empty($chk['id']) && ($rec['@hash'] == $chk['hash'])) {
		syslog(LOG_INFO, "B2B/Incoming {$rec['@id']} STABLE");
		return 1;
	}

	$b2b0 = [
		// 'id' => _ulid()
		// , 'license_id_source' => $l1['id']
		'license_id_target' => $_SESSION['License']['id']
		, 'created_at' => $rec['date_created']
		, 'updated_at' => date(\DateTime::RFC3339, $rec['last_updated'])
		, 'guid' => $rec['@id']
		, 'name' => sprintf('QBench Order %d', $rec['id'])
		, 'hash' => $rec['@hash']
		, 'meta' => json_encode($rec)
		, 'stat' => 200
	];

	// Order Status
	switch (strtoupper($rec['state'])) {
		case 'CANCELLED':
			$b2b0['stat'] = 410;
			break;
		case 'COMPLETED':
			$b2b0['stat'] = 307;
			break;
		case 'CREATED':
		case 'IN PROGRESS': // sub category of CREATED
		case 'ON HOLD':     // sub category of CREATED
			$b2b0['stat'] = 200;
			break;
	}

	if (empty($chk['id'])) {

		syslog(LOG_INFO, "B2B/Incoming/Create {$rec['@id']}");

		$b2b0['id'] = _ulid();

		// License = customer_account_id?
		// License = explode(',', strtolower($rec['email_to']));

		$l1 = $dbc->fetchRow('SELECT id FROM license WHERE (code = :l1 OR guid = :l1)', [
			':l1' => sprintf('qbench:%s', $rec['customer_account_id'])
		]);
		if (empty($l1['id'])) {

			printf("CREATE License: qbench:%s in %s\n", $rec['customer_account_id'], $rec['@id']);

			$l1 = [
				'id' => _ulid(),
				'code' => sprintf('qbench:%s', $rec['customer_account_id']),
				'guid' => sprintf('qbench:%s', $rec['customer_account_id']),
				'name' => sprintf('qbench:%s', $rec['customer_account_id']),
				'type' => 'X',
				'hash' => '-'
			];
			$dbc->insert('license', $l1);
		}

		$b2b0['license_id_source'] = $l1['id'];

		$dbc->insert('b2b_incoming', $b2b0);

	} else {

		$b2b0 = array_merge($b2b0, $chk);

		$update = [];
		$update['hash'] = $rec['@hash'];
		$update['stat'] = $b2b0['stat'];
		$update['meta'] = json_encode($rec);
		$update['updated_at'] = date(\DateTime::RFC3339, $rec['last_updated']);

		$filter = [];
		$filter['id'] = $b2b0['id'];

		$dbc->update('b2b_incoming', $update, $filter);

		// syslog(LOG_INFO, "B2B/Incoming/Update {$rec['@id']}");

	}

	// Importing WCIA Source Data
	if ( ! empty($rec['@source'])) {
		$src = $rec['@source'];
		foreach ($src['inventory_transfer_items'] as $b2b_item) {
			_qbench_b2b_import_wcia_item($dbc, $b2b0, $b2b_item);
		}
	}

	return 0;

}
/**
 *
 */
function _qbench_b2b_import_wcia(string $url) : ?array
{
	// @todo use \OpenTHC\CRE\WCIA::get()
	if ( ! preg_match('/^http.+/', $url)) {
		return null;
	}


	$req = _curl_init($url);
	$res = curl_exec($req);
	$inf = curl_getinfo($req);
	if (200 != $inf['http_code']) {
		return null;
	}

	$ret = json_decode($res, true);
	if (empty($ret)) {
		return null;
	}

	if ('WCIA Transfer Schema' == $ret['document_name']) {
		$ret['@context'] = 'https://cannabisintegratorsalliance.com/v2021/b2b';
		$ret['@source'] = $url;
	}

	return $ret;

}

function _qbench_b2b_import_wcia_item($dbc, $b2b, $b2b_item)
{
	$product_guid = $b2b_item['product']['id'] ?? $b2b_item['product_sku'];
	$product_name = $b2b_item['product']['name'] ?: $b2b_item['product_name'] ?: $b2b_item['inventory_name'];
	$variety_name = $b2b_item['variety']['name'] ?: $b2b_item['variety_name'] ?: $b2b_item['strain_name'];

	$P = new Product($dbc);
	if (empty($P['id'])) {
		$P->loadBy('guid', $product_guid);
	}
	if (empty($P['id'])) {
		$P->loadBy('name', $product_name);
	}
	if (empty($P['id'])) {

		$t0 = $b2b_item['inventory_category'];
		$t1 = $b2b_item['inventory_type'];

		$PT = new Product_Type($dbc, \OpenTHC\CRE\WCIA::map_product_type_ct2id($t0, $t1) );

		$P = new Product($dbc);
		$P['license_id'] = $_SESSION['License']['id'];
		$P['guid'] = ($product_guid ?: substr(_ulid(), 0, 16) );
		$P['name'] = $product_name;
		$P['stub'] = _text_stub($P['name']);

		$P['product_type_id'] = $PT['id'];
		$P['package_type'] = $PT['mode'];
		$P['package_unit_qom'] = floatval($b2b_item['unit_weight']);
		$P['package_unit_uom'] = $PT['unit'];
		if ('each' == $PT['mode']) {
			// $P['package_pack_qom'] = 'g';
			// $P['package_pack_uom'] = 'g';
		}

		$P->save('QBench/Import/Product/Create');

	}

	$V = new Variety($dbc); // $b2b_item['meta']['variety']['id'] ?? $_POST['variety'][$idx]);
	// $V->loadBy('name', $variety_name);
	$V->loadBy([
		'name' => $variety_name,
		'license_id' => $_SESSION['License']['id']
	]);
	if (empty($V['id'])) {
		$V = new Variety($dbc);
		$V['license_id'] = $_SESSION['License']['id'];
		$V['guid'] = $b2b_item['variety']['id'] ?? _ulid();
		$V['name'] = $variety_name;
		$V->save('QBench/Import/Variety/Create');
	}

	// Find in Inventory
	$inv0 = $dbc->fetchRow('SELECT * FROM inventory WHERE guid = :g0', [
		':g0' => $b2b_item['inventory_id']
	]);
	if (empty($inv0['id'])) {

		// CREATE
		$lot = [
			'guid' => $b2b_item['inventory_id'],
			'license_id' => $_SESSION['License']['id'],
			'license_id_source' => $b2b['license_id_source'],
			'product_id' => $P['id'],
			'variety_id' => $V['id'],
			'section_id' => '018NY6XC00SECT10N000000000',
			'qty' => $b2b_item['qty'],
			'qty_initial' => $b2b_item['qty'],
			'stat' => 200
		];

		// var_dump($lot);
		// syslog(LOG_NOTICE, "Order: '{$b2b['id']}'; Create Inventory '{$b2b_item['inventory_id']}' from WCIA Data");
		// $dbc->insert('inventory', $lot);

	} else {
		// UPDATE
		$lot = [
			'guid' => $b2b_item['inventory_id'],
			'license_id' => $_SESSION['License']['id'],
			'license_id_source' => $b2b['license_id_source'],
			'product_id' => $P['id'], // '018NY6XC00PR0DUCT000000000',
			'variety_id' => $V['id'], // '018NY6XC00VAR1ETY000000000',
			'section_id' => '018NY6XC00SECT10N000000000',
			'qty' => $b2b_item['qty'],
			'qty_initial' => $b2b_item['qty'],
			'stat' => 200
		];

		$dbc->update('inventory', $lot, [ 'id' => $inv0['id'] ]);

	}

}

/**
 *
 */
function _qbench_pull_report($dbc, $qbc)
{
	//
	// Reports
	$res = $qbc->get('/api/v1/report');
	var_dump($res);
	foreach ($res['data'] as $rec) {
		echo "Report: {$rec['id']} {$rec['title']}\n";
		// exit;
	}
	print_r($rec);
}

// Assay
// Now Get a Test
// $req = $qbc->get('/api/v1/test/%s');

// Now Get all Lab Results (Assays)
// GET <qbench-url>/qbench/api/v1/assay?page_num={pageNum}&page_size={pageSize}


/**
 *
 */
function _qbench_map_metric($dbc, $metric_key) : array
{


	static $metric_map = [
		  'a_bisabolol_percent' => '018NY6XC00LMQW96F7VFGSCTYK'
		, 'a_bisabolol_ug_g' => LAB_METRIC_SKIP
		, 'a_bisabolol' => LAB_METRIC_SKIP
		, 'a_humulene_percent' => '018NY6XC00LMQF9D59E5T0QA0A'
		, 'a_humulene_ug_g' => LAB_METRIC_SKIP
		, 'a_humulene' => LAB_METRIC_SKIP
		, 'a_pinene_percent' => '018NY6XC00LMCG3GPPN8QDAGAQ'
		, 'a_pinene_ug_g' => LAB_METRIC_SKIP
		, 'a_pinene' => LAB_METRIC_SKIP
		, 'a_terpinene_percent' => '01EEE1HSDKCQ1GP18N74BZ3KGE'
		, 'a_terpinene_ug_g' => LAB_METRIC_SKIP
		, 'a_terpinene' => LAB_METRIC_SKIP
		, 'a_terpineol_percent' => '018NY6XC00LMMQ4V3VTBQ83QWW'
		, 'a_terpineol_ug_g' => '018NY6XC00LMMQ4V3VTBQ83QWW'
		, 'a_terpineol' => '018NY6XC00LMMQ4V3VTBQ83QWW'
		, 'abamectin_b1a' => '018NY6XC00LMA5GGMB8GXE0SJW'
		, 'acephate' => '018NY6XC00LMME2KAJD5CJZCFC'
		, 'acequinocyl' => '018NY6XC00LMB7TEPP64SS0VXD'
		, 'acetamiprid' => '018NY6XC00LMHPJYYDQT7FCM8P'
		, 'acetone' => '018NY6XC00LM9HW5DZGD5KR55G'
		, 'acetonitrile' => '018NY6XC00STNQ0SR3G2XBMAYJ'
		, 'aerobic' => '018NY6XC00LMFPY3XH8NNXM9TH'
		, 'aldicarb_sulfone' => '018NY6XC00KRBFY2AHKHRXN58B'
		, 'aldicarb' => '018NY6XC00LME4KJM6Y8XP8WGA'
		, 'aminocarb' => '018NY6XC00X9FKBQTRMMT9CWYB'
		, 'arsenic_action_limit' => LAB_METRIC_SKIP
		, 'arsenic_lod' => LAB_METRIC_SKIP
		, 'arsenic_loq' => LAB_METRIC_SKIP
		, 'arsenic_mrl' => LAB_METRIC_SKIP
		, 'arsenic_ug_g' => '018NY6XC00LM4E4T6EPA7WPHDK'
		, 'arsenic' => '018NY6XC00LM4E4T6EPA7WPHDK'
		, 'atrazine_gc' => '018NY6XC009VDNXYVCFJ88GVF5' // @dedupe
		, 'atrazine_lc' => '018NY6XC009VDNXYVCFJ88GVF5' // @dedupe
		, 'atrazine' => '018NY6XC009VDNXYVCFJ88GVF5'
		, 'azoxystrobin' => '018NY6XC00KKW13KEN6JWKZNJF'
		, 'b_caryophyllene_percent' => '018NY6XC00LM9QSV7PQDRB1VEY'
		, 'b_caryophyllene_ug_g' => '018NY6XC00LM9QSV7PQDRB1VEY'
		, 'b_caryophyllene' => LAB_METRIC_SKIP
		, 'b_myrcene_percent' => '018NY6XC00LM0Q5E8PYHY9WQ57'
		, 'b_myrcene_ug_g' => '018NY6XC00LM0Q5E8PYHY9WQ57'
		, 'b_myrcene' => LAB_METRIC_SKIP
		, 'b_pinene_percent' => '018NY6XC00LM733KQWC064C0X8'
		, 'b_pinene_ug_g' => '018NY6XC00LM733KQWC064C0X8'
		, 'b_pinene' => LAB_METRIC_SKIP
		, 'benalaxyl' => '018NY6XC00JR79FV9HVKHWW5HQ'
		, 'benzene' => '018NY6XC00LMT7VRMWMXMH59Y5'
		, 'bifenazate' => '018NY6XC001CR4C8SJEZQE1NYD'
		, 'bifenthrin_gc' => '018NY6XC00LMPH4K88KC1PKJVJ'
		, 'bifenthrin_lc' => '018NY6XC00LMPH4K88KC1PKJVJ'
		, 'bifenthrin' => '018NY6XC00LMPH4K88KC1PKJVJ'
		, 'bile_tolerant' => '018NY6XC00LM638QCGB50ZKYKJ'
		, 'boron' => '018NY6XC000WMQVN35HCPYPW8W'
		, 'boscalid_gc' => '018NY6XC00LM3P767WQ0KSFARZ' // @dedupe
		, 'boscalid_lc' => '018NY6XC00LM3P767WQ0KSFARZ' // @dedupe
		, 'boscalid' => '018NY6XC00LM3P767WQ0KSFARZ'
		, 'bot_name' => LAB_METRIC_SKIP    // Ignore Unsure what this is
		, 'butafenacil' => '018NY6XC00QVEKQ8JCMP68DFJ7'
		, 'butane' => '018NY6XC00LMSTBW55VFR0QG56'
		, 'cadmium_action_limit' => LAB_METRIC_SKIP
		, 'cadmium_lod' => LAB_METRIC_SKIP
		, 'cadmium_loq' => LAB_METRIC_SKIP
		, 'cadmium_mrl' => LAB_METRIC_SKIP
		, 'cadmium_ug_g' => '018NY6XC00LMGNGNEW1XMNRS8S'
		, 'cadmium' => '018NY6XC00LMGNGNEW1XMNRS8S'
		, 'calcium' => '018NY6XC006SBTBXZ7J54HFQ0R'
		, 'camphene_percent' => '018NY6XC00LM5RP8VV8TQAJ92A'
		, 'camphene_ug_g' => '018NY6XC00LM5RP8VV8TQAJ92A'
		, 'camphene' => LAB_METRIC_SKIP
		, 'carbaryl' => '018NY6XC00LMZP42VJGA642TEB'
		, 'carbetamide' => '018NY6XC00MZ1K5XFDNA5BDGEP'
		, 'carbofuran' => '018NY6XC00LM7N4CCX5ZRVADDN'
		, 'carboxin' => '018NY6XC00VZJ24PN2VPPXX6P2'
		, 'carfentrazone_ethyl_nh4' => '018NY6XC00XFEAWWREZRVF68PE'
		, 'caryophyllene_oxide_percent' => '018NY6XC002GH0MJ4KFFBE79WN'
		, 'caryophyllene_oxide_ug_g' => '018NY6XC002GH0MJ4KFFBE79WN'
		, 'caryophyllene_oxide' => LAB_METRIC_SKIP
		, 'cbc_l' => LAB_METRIC_SKIP
		, 'cbc_mg_g' => LAB_METRIC_SKIP
		, 'cbc_mg_ml' => LAB_METRIC_SKIP
		, 'cbc_mg_serving' => LAB_METRIC_SKIP
		, 'cbc_percent' => '018NY6XC00LM50KG4SS3BDPAGX'
		, 'cbca_l' => LAB_METRIC_SKIP
		, 'cbca_mg_g' => LAB_METRIC_SKIP
		, 'cbca_mg_ml' => LAB_METRIC_SKIP
		, 'cbca_mg_serving' => LAB_METRIC_SKIP
		, 'cbca_percent' => '018NY6XC00LM74YZAGG90X06MC'
		, 'cbd_l' => LAB_METRIC_SKIP
		, 'cbd_mg_g' => LAB_METRIC_SKIP
		, 'cbd_mg_ml' => LAB_METRIC_SKIP
		, 'cbd_mg_serving' => LAB_METRIC_SKIP
		, 'cbd_percent' => '018NY6XC00LMK7KHD3HPW0Y90N'
		, 'cbda_l' => LAB_METRIC_SKIP
		, 'cbda_mg_g' => LAB_METRIC_SKIP
		, 'cbda_mg_ml' => LAB_METRIC_SKIP
		, 'cbda_mg_serving' => LAB_METRIC_SKIP
		, 'cbda_percent' => '018NY6XC00LMENDHEH2Y32X903'
		, 'cbdv_l' => LAB_METRIC_SKIP
		, 'cbdv_mg_g' => LAB_METRIC_SKIP
		, 'cbdv_mg_ml' => LAB_METRIC_SKIP
		, 'cbdv_mg_serving' => LAB_METRIC_SKIP
		, 'cbdv_percent' => '018NY6XC00LMZGPEH1Z4VY04RJ'
		, 'cbdva_l' => LAB_METRIC_SKIP
		, 'cbdva_mg_g' => LAB_METRIC_SKIP
		, 'cbdva_mg_ml' => LAB_METRIC_SKIP
		, 'cbdva_mg_serving' => LAB_METRIC_SKIP
		, 'cbdva_percent' => '018NY6XC00M27M46SED0ZZCSQZ'
		, 'cbg_l' => LAB_METRIC_SKIP
		, 'cbg_mg_g' => LAB_METRIC_SKIP
		, 'cbg_mg_ml' => LAB_METRIC_SKIP
		, 'cbg_mg_serving' => LAB_METRIC_SKIP
		, 'cbg_percent' => '018NY6XC00LMXRFMR5NJ35ZBAX'
		, 'cbga_l' => LAB_METRIC_SKIP
		, 'cbga_mg_g' => LAB_METRIC_SKIP
		, 'cbga_mg_ml' => LAB_METRIC_SKIP
		, 'cbga_mg_serving' => LAB_METRIC_SKIP
		, 'cbga_percent' => '018NY6XC00LMAKFJY80QDMWF7F'
		, 'cbl_l' => LAB_METRIC_SKIP
		, 'cbl_mg_g' => LAB_METRIC_SKIP
		, 'cbl_mg_ml' => LAB_METRIC_SKIP
		, 'cbl_mg_serving' => LAB_METRIC_SKIP
		, 'cbl_percent' => '018NY6XC00LMZZ776J7YTKR49R'
		, 'cbla_l' => LAB_METRIC_SKIP
		, 'cbla_mg_g' => LAB_METRIC_SKIP
		, 'cbla_mg_ml' => LAB_METRIC_SKIP
		, 'cbla_mg_serving' => LAB_METRIC_SKIP
		, 'cbla_percent' => '018NY6XC00T2ZJXXZA3HHXW6N3'
		, 'cbn_l' => LAB_METRIC_SKIP
		, 'cbn_mg_g' => LAB_METRIC_SKIP
		, 'cbn_mg_ml' => LAB_METRIC_SKIP
		, 'cbn_mg_serving' => LAB_METRIC_SKIP
		, 'cbn_percent' => '018NY6XC00LM3W3G1ERAF2QEF5'
		, 'cbna_l' => LAB_METRIC_SKIP
		, 'cbna_mg_g' => LAB_METRIC_SKIP
		, 'cbna_mg_ml' => LAB_METRIC_SKIP
		, 'cbna_mg_serving' => LAB_METRIC_SKIP
		, 'cbna_percent' => '018NY6XC00LMA46E79SNHBKR6H'
		, 'chlorantraniliprole' => '018NY6XC00WHBFGHPDBB756NTZ'
		, 'chlorfenapyr' => '018NY6XC00LMR0FZPVYAEJ8JET'
		, 'chloroform' => '018NY6XC00LMAA8QXZ8CD0QQMW'
		, 'chlorotoluron' => '018NY6XC00N5KT44W546GPVM5W'
		, 'chloroxuron' => '018NY6XC00BESMYZ2TS1G77DYH'
		, 'chlorpyrifos_gc' => '018NY6XC00LMXCM3VG0XGR6KAH' // @dedupe
		, 'chlorpyrifos_lc' => '018NY6XC00LMXCM3VG0XGR6KAH' // @dedupe
		, 'chlorpyrifos' => '018NY6XC00LMXCM3VG0XGR6KAH'
		, 'cis_nerolidol_percent' => '018NY6XC004NQKFWGHH2V2HPDT'
		, 'cis_nerolidol_ug_g' => '018NY6XC004NQKFWGHH2V2HPDT'
		, 'cis_nerolidol' => LAB_METRIC_SKIP
		, 'clofentezine' => '018NY6XC00LM230ECKQSRFZ2BE'
		, 'clothianidin' => '018NY6XC00E31T3XNT16BPBNN7'
		, 'coliform' => '018NY6XC00LMTMR8TN8WE86JVY'
		, 'comments' => LAB_METRIC_SKIP
		, 'copper' => '018NY6XC00JB22ZVQ47CBPZCMZ'
		, 'cyazofamid' => '018NY6XC001PN891J5H1HMQMWG'
		, 'cyclohexane' => '018NY6XC00LM6QHYX79AXVVRH1'
		, 'cycluron' => '018NY6XC00PP4F51A3YZBJ5DDK'
		, 'cyfluthrin' => '018NY6XC00LMCQDJ36Y13GX6W3'
		, 'cypermethrin' => '018NY6XC00LM7CX800BM0FSGJR'
		, 'daminozide' => '018NY6XC00LM8H6MET0WJ2YCV1'
		, 'ddvp' => '018NY6XC00LMXVVJP95SCEWYMJ' // Dichlorvos
		, 'delta_8_thc_l' => LAB_METRIC_SKIP
		, 'delta_8_thc_mg_g' => LAB_METRIC_SKIP
		, 'delta_8_thc_mg_ml' => LAB_METRIC_SKIP
		, 'delta_8_thc_mg_serving' => LAB_METRIC_SKIP
		, 'delta_8_thc_percent' => '018NY6XC00LM877GAKMFPK7BMC'
		, 'delta_three_carene_percent' => '018NY6XC00LMJ8HWPTK92118TJ'
		, 'delta_three_carene_ug_g' => '018NY6XC00LMJ8HWPTK92118TJ'
		, 'delta_three_carene' => LAB_METRIC_SKIP
		, 'diazinon' => '018NY6XC00LM1FYB8674M2X435'
		, 'dichloromethane' => '018NY6XC00LM7D70QAKTR6WM30'
		, 'dicrotophos' => '018NY6XC00FB8JT1PA317Z2MND'
		, 'diethofencarb' => '018NY6XC002GA28QZH4DPV4NF7'
		, 'dimethoate' => '018NY6XC00LMMGQYZ01JNTDSZ1'
		, 'dimethomorph' => '018NY6XC00LMYY49X6ZPKWMK0F'
		, 'dimoxystrobin' => '018NY6XC00FGBE9236HKC2Z99Y'
		, 'diuron' => '018NY6XC00TZB49XSAM9XMQ0C1'
		, 'e_coli' => '018NY6XC00LM7S8H2RT4K4GYME'
		, 'epoxiconazole' => '018NY6XC00NGT4P7T5BK3Y4WV6'
		, 'ethanol' => '018NY6XC00LMTBZ6MS529BRMDY'
		, 'ethiofencarb' => '018NY6XC00DYP6MHANHBMRXJSP'
		, 'ethoprophos' => '018NY6XC00LMYBVS4P9WE8MT73'
		, 'ethyl_acetate' => '018NY6XC00LMH5RPYTRCS5BQKJ'
		, 'ethyl_ether' => '018NY6XC00Z9M20PRDYBB5EXG3'
		, 'etofenprox' => '018NY6XC00LMJHEQ07C7YKJBFM'
		, 'etoxazole' => '018NY6XC00LMNPCTGHS6PVWKS3'
		, 'eucalyptol_percent' => '018NY6XC00LM2E59R89FKZEVY8'
		, 'eucalyptol_ug_g' => '018NY6XC00LM2E59R89FKZEVY8'
		, 'eucalyptol' => LAB_METRIC_SKIP
		, 'extr_distill' => LAB_METRIC_SKIP // Ignore
		, 'fenamidone' => '018NY6XC00C9ETHT8V2SDPXVZ5'
		, 'fenazaquin' => '018NY6XC0083TN9WFAX9CKABRV'
		, 'fenoxycarb' => '018NY6XC00LMGN496XNG04YCKA'
		, 'fenpyroximate' => '018NY6XC00LM1NGMNDNYD3R0HE'
		, 'fenuron' => '018NY6XC00K1S7Z4B7TS5VVM8A'
		, 'fipronil' => '018NY6XC00LM98WRGGCSFYYGVX'
		, 'flonicamid' => '018NY6XC00LMZMT6NYFV6QM9JH'
		, 'fluazinam' => '018NY6XC00WZF17ZP3878KKVNA'
		, 'fludioxonil' => '018NY6XC00LMZ91MVJB81J4JQT'
		, 'flufenacet' => '018NY6XC00VZX735DKKRTMBW6R'
		, 'fluometuron' => '018NY6XC0083YEGTKJYVJJ38V7'
		, 'flutolanil' => '018NY6XC00KZX7A23W14MT2074'
		, 'flutriafol' => '018NY6XC00LMCQGJHSAQRVR05X'
		, 'foreign_materials_pf' => '018NY6XC00LMA50497RDC53DB5'
		, 'fuberidazole' => '018NY6XC00BXDN38RRQFM83K2C'
		, 'furalaxyl' => '018NY6XC0015HX2C5EYMXAJ9SN'
		, 'furathiocarb' => '018NY6XC00YD57JR2J0QD9S2ZB'
		, 'gamma_terpinene_percent' => '018NY6XC00QJQVPK47XK0HMYGT'
		, 'gamma_terpinene_ug_g' => '018NY6XC00QJQVPK47XK0HMYGT'
		, 'gamma_terpinene' => LAB_METRIC_SKIP
		, 'geraniol_percent' => '018NY6XC00LM1FFPX84N11Y960'
		, 'geraniol_ug_g' => '018NY6XC00LM1FFPX84N11Y960'
		, 'geraniol' => LAB_METRIC_SKIP
		, 'guaiol_percent' => '018NY6XC00LMQQDFGS8QBAKJ3J'
		, 'guaiol_ug_g' => '018NY6XC00LMQQDFGS8QBAKJ3J'
		, 'guaiol' => LAB_METRIC_SKIP
		, 'heptane' => '018NY6XC00LM50MYZZY71MQ7BE'
		, 'hexane' => '018NY6XC00LM7EC335XECKPV3X'
		, 'hexythiazox' => '018NY6XC00LMADJX0GMMS5MXVB'
		, 'hydroxycarbofuran' => '018NY6XC000Z35NGMQK82PF009'
		, 'hydroxymitragynine_mg_g' => '018NY6XC00HKGZD7C613RNE0Z0'
		, 'hydroxymitragynine' => LAB_METRIC_SKIP
		, 'imazalil' => '018NY6XC00LMX1R3RFFRFZS8T4'
		, 'imidacloprid' => '018NY6XC00LMR9Z32S7WHPBZP9'
		, 'indoxacarb' => '018NY6XC00NWYNHMA169GDYEP2'
		, 'iprovalicarb' => '018NY6XC00CB0AGH0G6P24DN1Y'
		, 'iron' => '018NY6XC00V02P54TKQ21XT7RZ'
		, 'isobutane' => '018NY6XC00LMPTJEH2SHH45155'
		, 'isoprocarb' => '018NY6XC00J1J81N2749EYCRGF'
		, 'isoproturon' => '018NY6XC00KQAY3ND99X6GTJS7'
		, 'isopulegol_percent' => '018NY6XC00LMFG9ZMJNQJ9AE5F'
		, 'isopulegol_ug_g' => '018NY6XC00LMFG9ZMJNQJ9AE5F'
		, 'isopulegol' => LAB_METRIC_SKIP
		, 'kresoxym_methyl' => '018NY6XC00LM4VRHKTYTJJRDPW' // Spelled Wrong
		, 'l_fenchone_percent' => LAB_METRIC_SKIP
		, 'l_fenchone_ug_g' => LAB_METRIC_SKIP
		, 'l_fenchone' => LAB_METRIC_SKIP
		, 'lead_action_limit' => LAB_METRIC_SKIP
		, 'lead_lod' => LAB_METRIC_SKIP
		, 'lead_loq' => LAB_METRIC_SKIP
		, 'lead_mrl' => LAB_METRIC_SKIP
		, 'lead_ug_g' => '018NY6XC00LM6YBP4J5ASBWVNR'
		, 'lead' => '018NY6XC00LM6YBP4J5ASBWVNR'
		, 'limonene_percent' => '018NY6XC00LM6J8FQHSXARDVMZ'
		, 'limonene_ug_g' => '018NY6XC00LM6J8FQHSXARDVMZ'
		, 'limonene' => LAB_METRIC_SKIP
		, 'linalool_percent' => '018NY6XC00LMK42ZVHZYKNQ1P0'
		, 'linalool_ug_g' => '018NY6XC00LMK42ZVHZYKNQ1P0'
		, 'linalool' => LAB_METRIC_SKIP
		, 'lod' => LAB_METRIC_SKIP
		, 'loq' => LAB_METRIC_SKIP
		, 'magnesium' => '018NY6XC00665Q9X4K5GYHCMKA'
		, 'malathion' => '018NY6XC00LMEN8F7VNXYV7HCS'
		, 'mandipropamid' => '018NY6XC00SNPZ5RJVW2VGGSHW'
		, 'manganese' => '018NY6XC00F9TF4KHN8Q31HX1Q'
		, 'mefenacet' => '018NY6XC00B7Q87QDXQH9VS5VP'
		, 'mepronil' => '018NY6XC00M8V4J6PX1J33YM3E'
		, 'mercury_action_limit' => LAB_METRIC_SKIP
		, 'mercury_lod' => LAB_METRIC_SKIP
		, 'mercury_loq' => LAB_METRIC_SKIP
		, 'mercury_mrl' => LAB_METRIC_SKIP
		, 'mercury_ug_g' => '018NY6XC00LM10ZPAN42R490W3'
		, 'mercury' => '018NY6XC00LM10ZPAN42R490W3'
		, 'metalaxyl' => '018NY6XC00LMMFPYJ25XC5QTTQ'
		, 'methabenzthiazuron' => '018NY6XC00XK2KR405RZSEGVJA'
		, 'methamidophos' => '018NY6XC00W08XEQ3QM1ZG0TP6'
		, 'methanol' => '018NY6XC00LMYC6MEJARSBRGW8'
		, 'methiocarb' => '018NY6XC00LMC4048KGG4SR6WF'
		, 'methomyl' => '018NY6XC00LM7WBZ76X1E3T868'
		, 'methoprotryne' => '018NY6XC00F32T5F8138M3XGP8'
		, 'methoxyfenozide' => '018NY6XC008X42NV5NC486K40V'
		, 'mexacarbate' => '018NY6XC00V3224FZN904CV8KZ'
		// Some Company have this as as Isomer1 and Isomer2 internally
		// But only puts one into QBench so we put it all in Isomer1
		, 'mgk_264' => '018NY6XC00LMCQ7DX02S94RMM7'
		, 'mitragynine_mg_g' => '018NY6XC00V3M3MSYQMV9RCMCE'
		, 'mitragynine' => LAB_METRIC_SKIP
		, 'moisture' => '018NY6XC00LM0PXPG4592M8J14'
		, 'molybdenum' => '018NY6XC00H5A6PZTZZPH4S5Y1'
		, 'monocrotophos' => '018NY6XC00QVVXQP061W2CPV05'
		, 'myclobutanil' => '018NY6XC00LMN56HSR1X5ACEJB'
		, 'naled' => '018NY6XC00LMSCF0SS8VVJ9DE5'
		, 'nitenpyram' => '018NY6XC00H4N32NPA0CS61N92'
		// , 'ochratoxin_a' => '01EDPTGHG0NDY33JDVXVPEWYXN' // Our is A +H
		, 'ochratoxin_a' => '018NY6XC00LMK15566W1G0ZH5X'
		, 'ocimene_1_percent' => '018NY6XC00LMPS11DW5VC5ZDF6'
		, 'ocimene_1_ug_g' => '018NY6XC00LMPS11DW5VC5ZDF6'
		, 'ocimene_1' => LAB_METRIC_SKIP
		, 'omethoate' => '018NY6XC00DBBXM932467MEQRD'
		, 'other_comments' => LAB_METRIC_SKIP
		, 'oxadixyl' => '018NY6XC00Z3253X3QJKK494CH'
		, 'oxamyl' => '018NY6XC00LM83VNPJMHTKX5F0'
		, 'p_cymene_percent' => '018NY6XC00LMQW6Q8FE142912R'
		, 'p_cymene_ug_g' => '018NY6XC00LMQW6Q8FE142912R'
		, 'p_cymene' => LAB_METRIC_SKIP
		, 'paclobutrazol' => '018NY6XC00LMV3YF9F83621G84'
		, 'parathion_methyl' => '018NY6XC00LM4N6RPDAC97NM9V' // '01G0HSPKX1C4MX26E0RJGECRQB' // Evaluate?
		, 'pentane' => '01FRK0CC1G0SMZT4RW3WR8QBA1'
		, 'permethrin_nh4' => '018NY6XC00LMXSM3QAXV8HQD5F'
		, 'permethrins' => '018NY6XC00LM3ZJH23WAKV7JEB'
		, 'phosmet' => '018NY6XC00LMZ95MW0N3JPZ056'
		, 'phosphorus' => '018NY6XC00GMSF4B6WEDX137VG'
		, 'picoxystrobin' => '018NY6XC00R5ETKRJAG59TFRJX'
		, 'piperonyl_butoxide' => '018NY6XC00LM6VF2D0V998AY9Q'
		, 'pirimicarb' => '018NY6XC00TZW1AB3MDBJYNA2Q'
		, 'plus_cedrol_percent' => LAB_METRIC_SKIP
		, 'plus_cedrol_ug_g' => LAB_METRIC_SKIP
		, 'plus_cedrol' => LAB_METRIC_SKIP
		, 'potassium' => '018NY6XC004RHQ7NBP8K4VE4AN'
		, 'prallethrin' => '018NY6XC00LMKX28NVG7PJT5WJ'
		, 'prometon' => '018NY6XC00HEVERTDA65N8HCGV'
		, 'propamocarb' => '018NY6XC001B5YTZKMR4BVTQCT'
		, 'propane' => '018NY6XC00LMCK0YZ3T76QWMNF'
		, 'propanol' => '018NY6XC00GYJ0DT8GS71YXYR2'
		, 'propargite' => '018NY6XC0007FM2S2X7DK713M0'
		, 'propiconazole' => '018NY6XC00LM6T0NCQGXBCSNCS'
		, 'propoxur' => '018NY6XC00LMD2VKZ8FHZ3F3X8'
		, 'pymetrozine' => '018NY6XC0076C9C0V96ETDQH3E'
		, 'pyracarbolid' => '018NY6XC00KVC18KHKD7NVBET0'
		, 'pyraclostrobin' => '018NY6XC00E5NEVSFCR2AC8H1B'
		, 'pyrethrin_i_gc' => '018NY6XC00A4CC4C21KRAMGQE0'
		, 'pyrethrin_i' => '018NY6XC00A4CC4C21KRAMGQE0'
		, 'pyrethrin_ii' => '018NY6XC00FE5KW2ZCRY7ED5WG'
		, 'pyrethrins_lc' => '01FRK0CC1G0SMZT4RW3WR8QB9W'
		, 'pyridaben' => '018NY6XC00LMH66XZD64ZDTHZW'
		, 'pyripoxyfen' => '018NY6XC0032DMCC1E0SQAGB8V' // Actually Spelled: Pyriproxyfen
		, 'quinoxyfen' => '018NY6XC003PXZ5PXNTVK0Z1VG'
		, 'rotenone' => '018NY6XC00P9BP9K9YZRC3009W'
		, 'salmonella' => '018NY6XC00LMS96WE6KHKNP52T'
		, 'sample_density' => LAB_METRIC_SKIP
		, 'sample_mass' => LAB_METRIC_SKIP
		, 'sample_volume' => LAB_METRIC_SKIP
		, 'sodium' => '018NY6XC00E5ZT93DF9ANMG29K'
		, 'spinosad_a' => '018NY6XC00LMMYVTVKPR0V8C0F'
		, 'spinosad_d' => '018NY6XC00LMPNZXG9Z9YNFFX9'
		, 'spinosads' => '018NY6XC00LMKF9QEXJGS0HGHM' // Pluralize in OT data
		, 'spiromesifen' => '018NY6XC00LMT9BF2M636RZBZX'
		, 'spirotetramat' => '018NY6XC00LMWDVDHEYRS6058S'
		, 'spiroxamine' => '018NY6XC00LMQ6AM5TE0FYPN2R'
		, 'stems_comments' => LAB_METRIC_SKIP
		, 'sulfur' => '018NY6XC000ZP3Q6CWW9AQ6224'
		, 'tebuconazole' => '018NY6XC00LMT8QJD3BG6CNXA8'
		, 'tebufenozide' => '018NY6XC005BCZ2QCXPDT081G9'
		, 'tebuthiuron' => '018NY6XC00RQKY5AYGVY554DRK'
		, 'terpinolene_percent' => '018NY6XC00LMBFR51SFFGQJXRF'
		, 'terpinolene_ug_g' => '018NY6XC00LMBFR51SFFGQJXRF'
		, 'terpinolene' => LAB_METRIC_SKIP
		, 'thc_l' => LAB_METRIC_SKIP
		, 'thc_mg_g' => LAB_METRIC_SKIP
		, 'thc_mg_ml' => LAB_METRIC_SKIP
		, 'thc_mg_serving' => LAB_METRIC_SKIP
		, 'thc_percent' => '018NY6XC00LM49CV7QP9KM9QH9'
		, 'thca_l' => LAB_METRIC_SKIP
		, 'thca_mg_g' => LAB_METRIC_SKIP
		, 'thca_mg_ml' => LAB_METRIC_SKIP
		, 'thca_mg_serving' => LAB_METRIC_SKIP
		, 'thca_percent' => '018NY6XC00LMB0JPRM2SF8F9F2'
		, 'thcv_l' => LAB_METRIC_SKIP
		, 'thcv_mg_g' => LAB_METRIC_SKIP
		, 'thcv_mg_ml' => LAB_METRIC_SKIP
		, 'thcv_mg_serving' => LAB_METRIC_SKIP
		, 'thcv_percent' => '018NY6XC00LMEXWB3ENZ1MK7R4'
		, 'thcva_l' => LAB_METRIC_SKIP
		, 'thcva_mg_g' => LAB_METRIC_SKIP
		, 'thcva_mg_ml' => LAB_METRIC_SKIP
		, 'thcva_mg_serving' => LAB_METRIC_SKIP
		, 'thcva_percent' => '018NY6XC00LMWV6T4FB28F9JMH'
		, 'thiacloprid' => '018NY6XC00LMMFQB5HBHJBQ9BS'
		, 'thiamethoxam' => '018NY6XC00LMCH7YXS32M4PNZF'
		, 'thiobencarb' => '018NY6XC00XFJV2A15SWXR8AS0'
		, 'thiophanate_methyl' => '018NY6XC008N5MR09YY8T1HRMR'
		, 'toluene' => '018NY6XC00LMGG9JR3SM0MEDGQ'
		, 'total_aflatoxins' => '018NY6XC00LMR9PB7SNBP97DAS' // Calculated?
		, 'total_cannabinoids_mg_g' => LAB_METRIC_SKIP
		, 'total_cannabinoids_mg_ml' => LAB_METRIC_SKIP
		, 'total_cannabinoids_percent' => '018NY6XC00SAE8Q4JSMF40YSZ3'
		, 'total_cannabinoids_serving' => LAB_METRIC_SKIP
		, 'total_cbd_mg_g' => LAB_METRIC_SKIP
		, 'total_cbd_mg_ml' => LAB_METRIC_SKIP
		, 'total_cbd_mg_serving' => LAB_METRIC_SKIP
		, 'total_cbd_percent' => '018NY6XC00DEEZ41QBXR2E3T97'
		, 'total_terpenes_percent' => LAB_METRIC_SKIP
		, 'total_terpenes_ug_g' => LAB_METRIC_SKIP
		, 'total_terpenes' => LAB_METRIC_SKIP
		, 'total_thc_mg_g' => LAB_METRIC_SKIP
		, 'total_thc_mg_ml' => LAB_METRIC_SKIP
		, 'total_thc_mg_serving' => LAB_METRIC_SKIP
		, 'total_thc_percent' => '018NY6XC00PXG4PH0TXS014VVW'
		, 'total_unit_serving' => LAB_METRIC_SKIP
		, 'trans_nerolidol_percent' => '018NY6XC00LMJ3HV06KJXPR9F3'
		, 'trans_nerolidol_ug_g' => '018NY6XC00LMJ3HV06KJXPR9F3'
		, 'trans_nerolidol' => LAB_METRIC_SKIP
		, 'tricyclazole' => '018NY6XC00EFCPDSJZ8XM6591M'
		, 'trifloxystrobin' => '018NY6XC00LMRG0A40VCNVW3YX'
		, 'triflumizole' => '018NY6XC00H7A0GKSCJ1PKN9NE'
		, 'uniconazole' => '018NY6XC00T7NND9V8G2Q3G095'
		, 'unit_weight' => LAB_METRIC_SKIP
		, 'units' => LAB_METRIC_SKIP
		, 'vamidothion' => '018NY6XC00E2EHRX8HHRQ5KDDX'
		, 'water_activity' => '018NY6XC00LMHF4266DN94JPPX'
		, 'xylene' => '018NY6XC00LMW1FC0RA14FZ3PF'
		, 'yeast_and_mold' => '018NY6XC00LMCPKZ3QB78GQXWP'
		, 'zinc' => '018NY6XC00MGZPMK6TJWAYY8VF'
		, 'zoxamide' => '018NY6XC00Q04NWA7NDVETQZES'
	];

	$metric_key = strtolower($metric_key);

	$metric_ulid = $metric_map[$metric_key];

	if (empty($metric_ulid)) {
		// Ignore the Pass/Failers
		if (preg_match('/_pf$/', $metric_key)) {
			$metric_ulid = LAB_METRIC_SKIP;
		}
	}

	if (LAB_METRIC_SKIP == $metric_ulid) {
		return [
			'id' => $metric_ulid,
			'name' => '-skip-',
		];
	}

	if (empty($metric_ulid)) {
		throw new \Exception(sprintf('Invalid Lab Metric Key for "%s", create? [BIQ-346]', $metric_key));
	}

	$sql = <<<SQL
	SELECT id, name, type, lab_metric_type_id
	  , meta->>'uom' AS uom
	  , meta AS meta
	FROM lab_metric
	WHERE id = :lm0
	SQL;

	$lm0 = $dbc->fetchRow($sql, [
		':lm0' => $metric_ulid
	]);

	return $lm0;

}


/**
 * Parse CLI Options
 */
function _cli_options()
{
	$doc = <<<DOC
	BONG CRE CCRS Upload Tool
	Usage:
		import-qbench --company=<ID> --license=<ID> [options]

	Options:
		--company=<ID>
		--license=<ID>
		--object=<LIST>
		--object-id=<ID>
		--page=<NUM>
	DOC;

	$res = Docopt::handle($doc, [
		'help' => true,
		'optionsFirst' => true,
	]);
	$opt = $res->args;

	if (empty($opt['--company'])) {
		echo "Say --company=COMPANY_D\n";
	}

	if (empty($opt['--license'])) {
		echo "Say --license=LICENSE_ID\n";
	}

	if (empty($opt['--object'])) {
		$opt['--object'] = explode(',', 'license,contact,b2b,sample,result');
	} else {
		$opt['--object'] = explode(',', $opt['--object']);
	}

	if ( ! empty($opt['--object-id'])) {
		$_ENV['object-id'] = $opt['--object-id'];
	}

	if (empty($opt['--page'])) {
		$opt['--page'] = 1;
	}

	return $opt;

}
