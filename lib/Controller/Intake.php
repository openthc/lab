<?php
/**
 * Process Sample Intake from Open/Semi-Public Portal
 *
 * SPDX-License-Identifier: GPL-3.0-only
 *
 * This file is part of OpenTHC Laboratory Portal
 *
 * OpenTHC Laboratory Portal is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published by
 * the Free Software Foundation.
 *
 * OpenTHC Laboratory Portal is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OpenTHC Laboratory Portal.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace OpenTHC\Lab\Controller;

use OpenTHC\Company;

class Intake extends \OpenTHC\Lab\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$dbc_auth = _dbc('auth');
		$C0 = $dbc_auth->fetchRow('SELECT id, name, dsn FROM auth_company WHERE id = :c0', [
			':c0' => $_GET['c']
		]);
		if (empty($C0['id'])) {
			_exit_html_fail('<h1>Invalid Link [LCI-036]</h1>', 400);
		}

		$dbc_user = _dbc($C0['dsn']);
		$L0 = $dbc_user->fetchRow('SELECT id, name FROM license WHERE id = :l0', [
			':l0' => $_GET['l']
		]);
		if (empty($L0['id'])) {
			_exit_html_fail('<h1>Invalid Link [LCI-045]</h1>', 400);
		}


		$data = $this->loadSiteData();

		$sql = 'SELECT id, name FROM product_type WHERE stat = 200 ORDER BY name';
		$data['product_type'] = $dbc_user->fetchMix($sql);

		return $RES->write( $this->render('intake.php', $data) );

	}

	/**
	 *
	 */
	function post($REQ, $RES, $ARG)
	{
		switch ($_POST['a']) {
			case 'lab-intake-save':

				$dbc = $this->_container->DBC_User;

				$dbc->query('BEGIN');

				$b2b_sale = [];
				$b2b_sale['id'] = _ulid();
				$b2b_sale['license_id_source'] = $_POST['license_id'];
				$b2b_sale['license_id_target'] = $_SESSION['License']['id']; // // $_POST['license_id'];
				$b2b_sale['stat'] = 100;
				$b2b_sale['name'] = sprintf('Sold By: %s', $_POST['license_id']);
				$b2b_sale['hash'] = md5(json_encode($b2b_sale));
				$dbc->insert('b2b_incoming', $b2b_sale);

				foreach ($_POST['product_type'] as $idx => $pt) {

					if (empty($pt)) {
						continue;
					}

					$_POST['product_name'][$idx] = trim($_POST['product_name'][$idx]);
					$_POST['variety_name'][$idx] = trim($_POST['variety_name'][$idx]);

					// Find Product
					$P0 = $dbc->fetchRow('SELECT id FROM product WHERE name = :n AND product_type_id = :pt', [
						':n' => $_POST['product_name'][$idx]
						, ':pt' => $_POST['product_type'][$idx]
					]);
					if (empty($P0['id'])) {
						$P0 = [];
						$P0['id'] = _ulid();
						$P0['guid'] = $P0['id'];
						$P0['license_id'] = $_SESSION['License']['id'];
						$P0['product_type_id'] = $_POST['product_type'][$idx];
						$P0['name'] = $_POST['product_name'][$idx];
						$P0['stub'] = __text_stub($P0['name']);
						$dbc->insert('product', $P0);
					}

					// Find Variety
					$V0 = $dbc->fetchRow('SELECT id FROM variety WHERE name = :n', [ ':n' => $_POST['variety_name'][$idx] ]);
					if (empty($V0['id'])) {
						$V0['id'] = _ulid();
						$V0['guid'] = $V0['id'];
						$V0['license_id'] = $_SESSION['License']['id'];
						$V0['name'] = $_POST['variety_name'][$idx];
						$dbc->insert('variety', $V0);
					}

					$L0 = [];
					$L0['id'] = _ulid();
					$L0['guid'] =$L0['id'];
					$L0['license_id'] = $_SESSION['License']['id'];
					$L0['section_id'] = '018NY6XC00SECT10N000000000';
					$L0['product_id'] = $P0['id'];
					$L0['variety_id'] = $V0['id'];
					$L0['stat'] = 200;
					$L0['qty'] = floatval($_POST['qty'][$idx]);
					$L0['hash'] = md5(json_encode($L0));

					$dbc->insert('inventory', $L0);

					$b2b_item = [];
					$b2b_item['id'] = _ulid();
					$b2b_item['b2b_incoming_id'] = $b2b_sale['id'];
					$b2b_item['lot_id'] = $L0['id'];
					$b2b_item['qty'] = floatval($_POST['qty'][$idx]);
					$b2b_item['name'] = '-';
					$b2b_item['hash'] = md5(json_encode($b2b_item));

					$dbc->insert('b2b_incoming_item', $b2b_item);

				}

				$dbc->query('COMMIT');

		}

	}

}
