<?php
/**
 * Adds Menu Items
 */

namespace OpenTHC\Lab\Middleware;

class Menu extends \OpenTHC\Middleware\Base
{
	function __invoke($REQ, $RES, $NMW)
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

		$this->_container->view['menu'] = $menu;

		$RES = $NMW($REQ, $RES);

		return $RES;

	}

}

/* Determine Selected ?
{#
<?php
$menu_list = App_Menu::getMenu('page');
foreach ($menu_list as $menu) {

	if (empty($menu['id'])) {
		$menu['id'] = 'menu-' . trim(preg_replace('/[^\w]+/', '-', $menu['link']), '-');
	}

	echo '<li><a ';

	if ($menu['link'] == substr(Radix::$path, 0, strlen($menu['link']))) { // == substr($menu['link'], $l)) {
		echo ' class="hi"';
	}

	echo ' id="' . $menu['id'] . '"';
	echo ' href="' . $menu['link'] . '">';
	echo $menu['name'];
	echo '</a></li>';

}

?>
#}

*/
