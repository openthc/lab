<?php
/**
 * Home Controller
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

namespace App\Controller;

class Dashboard extends \App\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$data = array(
			'Page' => array('title' => 'Dashboard'),
			'Company' => $_SESSION['Company'],
			'License' => $_SESSION['License'],
			'Contact' => $_SESSION['Contact'],
		);
		$data = $this->loadSiteData($data);

		// $file = 'page/home-supply.html'; // @deprecated, merge to main dashboard

		return $RES->write( $this->render('dashboard.php', $data) );

	}

}
