
<form action="/search" method="get">
<div class="container mt-2">
<div class="input-group">
	<input class="form-control" name="q" placeholder="Search Name, Company, License, City, etc" type="text" value="">
	<button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Search</button>
</div>
</div>
</form>

<?php
foreach ($data['result_list'] as $r) {
?>
	<div>
		<a href="<?= $r['link'] ?>"><?= $r['id'] ?></a>
	</div>
<?php
}
?>
