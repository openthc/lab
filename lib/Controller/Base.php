<?php
/**
 * Base Controller
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
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller;

class Base extends \OpenTHC\Controller\Base
{
	/**
	 *
	 */
	function loadSiteData($data=[]) : array
	{
		$base = [
			'Site' => [
				'hostname' => $_SERVER['SERVER_NAME'],
			],
			'OpenTHC' => [],
			'menu' => $this->makeMenu(),
		];

		$data = array_merge($base, $data);

		return $data;
	}

	/**
	 *
	 */
	function loadSearchPageData(array $data) : array
	{
		$_GET['p'] = max(1, intval($_GET['p']));

		$data['search_page'] = [
			'cur' => 1,
			'max' => 0,
			'older' => (intval($_GET['p']) - 1),
			'newer' => (intval($_GET['p']) + 1),
			'limit' => 100,
		];
		$data['search_sort'] = [
			'col' => 'id',
			'dir' => 'asc'
		];

		return $data;

	}

	function makeMenu() : array
	{
		$menu = array(
			'home_link' => '/',
			'home_html' => '<i class="fas fa-home"></i>',
			'show_search' => false,
			'main' => array(),
			'page' => array(
				array(
					'link' => '/auth/open',
					'html' => '<i class="fas fa-sign-in-alt text-success"></i>',
				)
			),
		);

		$auth = false;
		if (!empty($_SESSION['Contact']['id'])) {
			$auth = true;
		}
		if (!empty($_SESSION['pipe-token'])) {
			$auth = true;
		}

		if ($auth) {

			$menu['home_link'] = '/dashboard';
			$menu['main'] = array();
			$menu['show_search'] = true;

			$menu['main'][] = array(
				'link' => '/sample',
				'html' => '<i class="fas fa-flask"></i> Samples',
			);

			$menu['main'][] = array(
				'link' => '/result',
				'html' => '<i class="fas fa-check-square"></i> Results',
			);

			$menu['main'][] = array(
				'link' => '/report',
				'html' => '<i class="fa-solid fa-file-signature"></i> Reports',
			);

			$menu['page'] = array(
				[
					'link' => '/config',
					'html' => '<i class="fas fa-cogs"></i>',
				],
				[
					'link' => '/auth/shut',
					'html' => '<i class="fas fa-power-off"></i>',
				]
			);
		}

		return $menu;

	}

}
