<?php
/**
 * Configure Sample Stuff
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller\Config;

class Sample extends \OpenTHC\Lab\Controller\Base
{
	const BASE_OPTION_KEY = 'lab-sample-seq-format';

	function __invoke($REQ, $RES, $ARG)
	{

		$dbc = $this->_container->DBC_User;

		switch ($_POST['a']) {
			// v0
			case 'reset-seq-d':
			case 'reset-seq-g':
			case 'reset-seq-y':
			case 'reset-seq-q':
			case 'reset-seq-m':

				$c = substr($_POST['a'], -1);

				$s = sprintf('seq_%s_%s', $_SESSION['Company']['id'], $c );
				$s = strtolower($s);

				$d = intval($_POST[sprintf('seq-%s-min', $c)]);
				$d = max(1, $d);

				$res = $dbc->query(sprintf('DROP SEQUENCE IF EXISTS %s', $s));
				$res = $dbc->query(sprintf('CREATE SEQUENCE %s MINVALUE %d START WITH %d', $s, $d, $d ));

				$res = $dbc->query('SELECT setval(:s, :d, false)', [
					':s' => $s,
					':d' => $d,
				]);

				var_dump($res);

			break;
			// v1
			case 'reset-seq-018NY6XC00SEQH9174ZH0DV5DQ':
			case 'reset-seq-018NY6XC00SEQCYWPQKDX1A37D':
			case 'reset-seq-018NY6XC00SEQWYYHWTB9DA4H1':
			case 'reset-seq-018NY6XC00SEQY40MRNEYXG414':
			case 'reset-seq-018NY6XC00SEQSQ2Q3HGEWKVPJ':
				$Seq = new \OpenTHC\Lab\Sequence($_SESSION['Company']['id'], $dbc);
				$Seq->setTimeZone($_SESSION['tz']);
				preg_match('/^reset-seq-(\w+)$/', $_POST['a'], $matches);
				$d = $_POST[sprintf('seq-%s-min', $matches[1])];
				$d = intval($d);
				$res = $Seq->resetSequence($matches[1], $d);

				var_dump($res);
				return $RES->withRedirect($_SERVER['REQUEST_URI']);
				break;
			// v2
			case 'reset-seq-company':
			case 'reset-seq-license':
				preg_match('/^reset-seq-(\w+)$/', $_POST['a'], $namespace_match);
				switch ($namespace_match[1]) {
					case 'license':
						$namespace = $_SESSION['License']['id'];
						break;
					case 'company':
					default:
						$namespace = $_SESSION['Company']['id'];
				}

				// Find the appropriate ulid-keyd symbol
				foreach ($_POST as $k => $v) {
					if (preg_match('/^seq-(\w{26})-min$/', $k, $matches)) {
						$symbol = $matches[1];
						$key = $k;
					}
				}

				$d = $_POST[$key];
				$d = intval($d);

				$Seq = new \OpenTHC\Lab\Sequence($namespace, $dbc);
				$Seq->setTimeZone($_SESSION['tz']);
				$res = $Seq->resetSequence($symbol, $d);
				break;
			case 'update-seq-format':
				$key = self::BASE_OPTION_KEY;
				$val = trim($_POST[$key]);
				$val = json_encode($val);
				$chk = $dbc->fetchRow('SELECT * FROM base_option WHERE key = :k', [ ':k' => $key ]);
				if (empty($chk)) {
					$dbc->insert('base_option', [
						'id' => _ulid(),
						'key' => self::BASE_OPTION_KEY,
						'val' => $val,
					]);
				} else {
					$dbc->update('base_option', [ 'val' => $val ], [ 'key' => $key ]);
				}
			break;
		}

		// Sequence table holds namespaced copies of $seq_data
		$seq_table = [];
		foreach ([ $_SESSION['License']['id'], $_SESSION['Company']['id'] ] as $namespace) {
		foreach ([ 'G','Y','Q','M','D'] as $c) { // $idx=0; $idx<4; $idx++) {
			try {

				$s = sprintf('seq_%s_%s', $_SESSION['Company']['id'], $c );
				$s = sprintf('seq_%s_%s', $namespace, $c );
				$s = strtolower($s);
				$arg = [ ':s' => $s ];

				// $seq_data[$idx] = $dbc->fetchOne(sprintf('SELECT currval(%s)', $s));
				// $seq_data[$idx] = $dbc->fetchOne('SELECT currval(:s)', $arg);
				// $seq_data[$idx] = $dbc->fetchOne('SELECT nextval(:s)', $arg);
				$seq_data[$c] = $dbc->fetchOne(sprintf('SELECT last_value FROM "%s"', $s));
				$seq_table[$namespace][$c] = $seq_data[$c];

			} catch (\Exception $e) {
				// Ignore
				// _exit_html($e->getMessage());
				$err = $e->getMessage();
				$seq_data[$c] = '-not-set-';
				$seq_table[$namespace][$c] = '-not-set-';
			}
		}
		}

		// $Company->setOption('sample-id-seq', '$YY$MA$SEQ_M');
		$Seq = new \OpenTHC\Lab\Sequence($_SESSION['Company']['id'], $dbc);
		$Seq->setTimeZone($_SESSION['tz']);
		$peek = $Seq->peek();

		$data = $this->loadSiteData();
		$data['Page']['title'] = 'Config :: Samples';

		$val = $dbc->fetchOne('SELECT val FROM base_option WHERE key = :k', [ ':k' => self::BASE_OPTION_KEY ]);
		$data['seq_format'] = json_decode($val);
		$data['seq_peek'] = $peek;

		$data['seq'] = [
			'YYYY' => date('Y'),
			'YY' => date('y'),
			'MM' => date('m'),
			'MA' => chr(64 + date('m')),
			'DD' => date('d'),
			'DDD' => sprintf('%03d', date('z') + 1),
			'HH' => date('H'),
			'II' => date('i'),
			'SS' => date('s'),
			'g' => $seq_data['G'],
			'y' => $seq_data['Y'],
			'y6' => sprintf('%06d', $seq_data['Y']),
			'q' => $seq_data['Q'],
			'q9' => sprintf('%09d', $seq_data['Q']),
			'm' => $seq_data['M'],
			'd' => $seq_data['D'],
		];
		$data['seq_company'] = $seq_table[ $_SESSION['Company']['id'] ];
		$data['seq_license'] = $seq_table[ $_SESSION['License']['id'] ];

		return $RES->write( $this->render('config/sample.php', $data) );

	}
}
