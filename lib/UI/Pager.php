<?php
/**
 * @see http://developer.yahoo.com/ypatterns/pattern.php?pattern=searchpagination
 */

namespace OpenTHC\Lab\UI;

class Pager
{
	// private $_show_size_full = 50;
	private $_show_size_prev = 5;
	private $_show_size_next = 5;

	private $item_count = 0;

	private $page_min = 1;
	private $page_max = -1;
	private $page_current = 1;

	function __construct($row_count, $page_size, $page_pick)
	{
		$this->item_count = intval($row_count);
		$this->page_max = ceil($this->item_count / $page_size);
		$this->page_current = max($this->page_min, intval($page_pick));
	}

	function getLink($p, $t, $c)
	{
		$l = http_build_query(array_merge($_GET, array('p' => $p )));

		$r = sprintf('<a class="btn %s" href="?%s">%s</a>', $c, $l, $t);

		return $r;
	}

	function getHTML()
	{
		$link_max = 20;

		// $page_size = intval($data['size']);
		// if ($page_size <= 0) {
		// 	return(0);
		// }

		if ($this->page_max <= 1) {
			return null;
		}

		$page_cur = max($this->page_min, $this->page_current);
		$page_cur = min($this->page_max, $page_cur);

		/**
			@param $p Page Value
			@param $t Text Content Value
			@param $c HTML CSS Class
		*/


		$out_min = max(1, $page_cur - 3); // Prefix
		$out_max = min($out_min + $link_max - 1, $this->page_max);
		if (($out_max - $out_min) < $link_max) {
			$out_min = max(1, $out_max - $link_max + 1);
		}

		$page_next_html = '&raquo; &raquo;';
		$page_prev_html = '&laquo; &laquo;';

		ob_start();

		// Output
		echo '<div class="btn-toolbar mb-2" role="toolbar" aria-label="page selection tool">';


		// Previous and First Pages
		echo '<div class="btn-group mr-2">';
		if ($page_cur > $this->page_min) {
			echo $this->getLink($page_cur - 1, $page_prev_html, 'btn-outline-secondary');
		} else {
			echo $this->getLink(null, $page_prev_html, 'btn-outline-secondary disabled');
		}
		if ($out_min > 1) {
			echo $this->getLink(1, '1&laquo;&laquo;', 'btn-outline-secondary');
		}
		echo '</div>';


		// Number Page Group List
		echo '<div class="btn-group mr-2">';
		for ($page_idx = $out_min; $page_idx <= $out_max; $page_idx++) {

			$t = null;
			$c = 'btn-outline-secondary';

			if ($page_idx < $page_cur) {
				$t = sprintf('&laquo;%d', $page_idx);
			} elseif ($page_idx == $page_cur) {
				$t = sprintf('&nbsp;%d&nbsp;', $page_idx);
				$c = 'btn-outline-primary active';
			} else {
				$t = sprintf('%d&raquo;', $page_idx);
			}

			echo $this->getLink($page_idx, $t, $c);

		}
		echo '</div>';

		// Draw Last & Next Buttons
		echo '<div class="btn-group mr-2">';
		if ($out_max < $this->page_max) {
			echo $this->getLink($this->page_max, sprintf('&raquo;&raquo;%d', $this->page_max), 'btn-outline-secondary');
		}
		if ($page_cur < $this->page_max) {
			echo $this->getLink($page_cur + 1, $page_next_html, 'btn-outline-secondary');
		} else {
			echo $this->getLink(null, $page_next_html, 'btn-outline-secondary disabled');
		}
		echo '</div>';

		// Show All Link
		$data['show_all'] = true;
		if ($data['show_all']) {
			echo '<div class="btn-group">';
			echo $this->getLink('ALL', 'Show All', 'btn-outline-secondary');
			echo '</div>';
		}

		echo '</div>';

		$ret = ob_get_clean();

		return $ret;
	}
}
