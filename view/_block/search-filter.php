<?php
/**
 * A fancy Search Filter
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

if (empty($data['search_field_list'])) {
	$data['search_field_list'] = [];
}

?>

<style>
.lab-result-filter-wrap {
	background-color: var(--gray-dark);
	border: 1px solid var(--gray);
	border-radius: 0.25rem;
	display: flex;
	flex-grow: 1;
	margin: 0;
	padding: 0.25rem;
}
.lab-result-filter-wrap .btn-outline-secondary {
	color: var(--light);
}
</style>

<form autocomplete="off">

<input name="sort" type="hidden" value="<?= __h($_GET['sort']) ?>">
<input name="sort-dir" type="hidden" value="<?= __h($_GET['sort-dir']) ?>">

<div class="lab-result-filter-wrap">
	<div class="input-group-wrap" style="flex: 1 1 auto; width: 100%;">
		<div class="input-group">
			<button class="btn btn-outline-secondary dropdown-toggle"
				data-bs-toggle="dropdown"
				id="dd-search-history"
				type="button" style="border-top-right-radius:0; border-bottom-right-radius:0;"><i class="fas fa-history"></i></button>
			<div class="dropdown-menu" id="sf-menu-history">
				...
			</div>
			<div class="form-control" id="sf-filter">
				<div id="sf-filter-list"></div>
				<div class="dropdown">
					<input class="dropdown-toggle" data-bs-toggle="dropdown" id="lab-result-filter" name="q" style="border: none;" type="text" value="<?= __h($_GET['q']) ?>">
					<div class="dropdown-menu">
						<?php
						foreach ($data['search_field_list'] as $k => $v) {
							printf('<button class="dropdown-item" type="button">%s</button>', $v);
						}
						?>
						<button class="dropdown-item" type="button">Lot ID</button>
						<button class="dropdown-item" type="button">Sample ID</button>
						<button class="dropdown-item" type="button">Result ID</button>
						<button class="dropdown-item" type="button">Variety</button>
						<button class="dropdown-item" type="button">Origin</button>
						<button class="dropdown-item" type="button">THC</button>
						<button class="dropdown-item" type="button">CBD</button>
						<button class="dropdown-item" type="button">Status</button>
					</div>
				</div>
			</div>
			<button class="btn btn-outline-secondary" name="a" type="submit" value="search"><i class="fas fa-search"></i></button>
		</div>
	</div>
	<div class="btn-group" style="margin-left: 1em; min-width: 12em;">
		<div class="btn-group">
			<button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" type="button">Sort Options</button>
			<ul class="dropdown-menu">
				<li><button class="dropdown-item" name="sort" value="result-id">Result ID</button></li>
				<li><button class="dropdown-item" name="sort" value="sample-id">Sample ID</button></li>
				<!-- <li><button class="dropdown-item" name="sort" value="variety">Variety</button></li> -->
				<li><button class="dropdown-item" name="sort" value="created-at">Created Date</button></li>
				<li><button class="dropdown-item" name="sort" value="license-origin">Origin License</button></li>
			</ul>
		</div>
		<?php
		switch (strtolower($_GET['sort-dir'])) {
			case 'asc':
			case 'az':
				echo '<button class="btn btn-outline-secondary" name="sort-dir" value="desc"><i class="fas fa-sort-amount-down"></i></button>';
				break;
			case 'desc':
			case 'za':
			default:
				echo '<button class="btn btn-outline-secondary" name="sort-dir" value="asc"><i class="fas fa-sort-amount-up"></i></button>';
		}
		?>
	</div>
</div>
</form>

<script>
$(function() {
	$('#dd-search-history').on('show.bs.dropdown', function() {
		// console.log(this);
		var $menu = $(this).parent().find('#sf-menu-history');
		$menu.empty();
		$menu.append('<h6 class="dropdown-header">Loading...</h6>');
		// Somwhere There is Search History?
	});

	$('#sf-filter .dropdown-item').on('click', function(e) {

		console.log(e);
		var txt = e.currentTarget.innerText;
		// Prepend This To the Input Wrap
		// Select the Operator of '='
		// Focus back to Input but not the DDrop Down One, now allow the Reguar Text INput
		console.log('Apply Filter to ');
		// <div><input name="filter-key[]" readonly type="text" value="${txt}"><input name="filter-val[]" type="search" value=""></div>
		var html = [];
		html.push('<div class="input-group input-group-sm">');
		html.push(`<div class="input-group-text"><input name="filter-key[]" readonly type="hidden" value="${txt}">${txt}</div>`);
		html.push('<input class="form-control" name="filter-val[]" type="search" value="">');
		html.push('</div>');
		$('#sf-filter-list').append(html.join(''));
	});

	$('#lab-result-filter').on('keyup', function() {
		// Start Filtering the Table?
		// Ajax Query?
		// Filter Table Rows and Hide Some?
	});
});
</script>
