<?php
/**
 */

?>

<nav class="navbar navbar-expand-md navbar-dark bg-dark sticky-top">

<a class="navbar-brand" href="/home"><?= $data['menu']['home_html'] ?></a>

<button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#menu-zero" aria-expanded="false" aria-controls="menu-zero">
	<span class="navbar-toggler-icon"></span>
</button>

<div class="navbar-collapse collapse" id="menu-zero">

<?php
if (!empty($data['menu']['main']) && is_array($data['menu']['main'])) {
	echo '<ul class="navbar-nav mr-auto">';
	foreach ($data['menu']['main'] as $mi) {
		printf('<li class="nav-item"><a class="nav-link" href="%s">%s</a></li>', $mi['link'], $mi['html']);
	}
	echo '</ul>';
}

// Search Option
if ($data['menu']['show_search']) {
?>
	<form action="/search" autocomplete="x" class="form-inline mr-auto" role="search">
	<div class="input-group">
		<input autocomplete="off" class="form-control" name="q" placeholder="Search..." type="text">
		<span class="input-group-append">
			<button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
		</span>
	</div>
	</form>
<?php
}
?>

<?php
if ($data['menu']['page']) {
?>
	<ul class="navbar-nav ml-auto">
	<?php
	foreach ($data['menu']['page'] as $mi) {
	?>
		<li class="nav-item"><a class="nav-link" href="{{ mi.link }}">{{ mi.html|raw }}</a></li>
	<?php
	}
	?>
	</ul>
<?php
}
?>

</div> <!-- /.navbar-collapse -->

</nav>
