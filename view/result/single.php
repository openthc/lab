<?php
/**
 * Render a Lab Result
 */

?>

<div class="row mt-4" style="position: relative;">
<div class="col-md-6">
	<h1><?= $data['Result']['id'] ?></h1>
	<h2>Sample: <a href="/sample/<?= $data['Sample']['id'] ?>"><?= $data['Sample']['id'] ?></a></h2>
</div>
<div class="col-md-6">
	<h3>Status: <?= $data['Result']['status'] ?></h3>
	<!-- @todo this is only relevant when it's a Lab showing this result -->
	<!-- <h3>Origin: {{ Sample.lot_id_source }}</h3> -->
</div>
<div class="r" style="position: absolute; right:0; top:0;">
	<form method="post" target="_blank">
		<button class="btn btn-outline-primary" name="a" type="submit" value="share"><i class="fas fa-share-alt"></i> Share</button>
		<?php
		if ($data['Result']['coa_file']) {
		?>
			<div class="btn-group">
				<button class="btn btn-outline-success" name="a" type="submit" value="coa-download"><i class="fas fa-download"></i> COA</button>
				<button class="btn btn-outline-success dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" type="button"></button>
				<div class="dropdown-menu dropdown-menu-right">
					<a class="dropdown-item" data-toggle="modal" data-target="#modal-coa-upload" href="#"><i class="fas fa-upload"></i> Upload COA</a>
				</div>
				<button class="btn btn-outline-secondary" name="a" type="submit" value="coa-create"><i class="fas fa-print"></i></button>
			</div>
		<?php
		} else {
		?>
			<div class="btn-group">
				<button class="btn btn-outline-warning" data-toggle="modal" data-target="#modal-coa-upload" name="a" title="No COA Uploaded" type="button" value="download-coa"><i class="fas fa-upload"></i> COA</button>
				<button class="btn btn-outline-secondary" name="a" type="submit" value="coa-create"><i class="fas fa-print"></i></button>
			</div>
		<?php
		}
		?>
	</form>
</div>
</div>

<div class="mb-2">
<?= $this->block('product-summary.php') ?>
</div>

<div class="mb-2">
<?= $this->block('potency-summary.php') ?>
</div>

<hr>

<style>
.result-metric-wrap {
	display: flex;
	flex-direction: row;
	flex-wrap: wrap;
	margin: 0;
	padding: 0;
}

.result-metric-data {
	flex: 1 0 33%;
	padding: 0 0.125rem 0.50rem 0.125rem;
	min-width: 15em;
}
</style>

<div>
<?php
foreach ($data['metric_type_list'] as $metric_type) {

	if (empty($data['MetricList'][ $metric_type['stub'] ])) {
		continue;
	}

?>
	<hr>
	<section>
		<h3><?= $metric_type['name'] ?></h3>
		<div class="result-metric-wrap">
			<?php
			foreach ($data['MetricList'][ $metric_type['stub'] ] as $result_data) {
				switch ($result_data['qom']) {
					case -3:
						$result_data['qom'] = '-ND-';
						$result_data['uom'] = '';
						break;
				}
			?>
				<div class="result-metric-data">
					<div class="input-group">
						<div class="input-group-text"><?= __h($result_data['name']) ?></div>
						<input class="form-control r" readonly style="font-weight: bold;" value="<?= __h($result_data['qom']) ?>">
						<div class="input-group-text"><?= __h($result_data['uom']) ?></div>
					</div>
				</div>
			<?php
			}
			?>
		</div>
	</section>
<?php
}
?>
</div>

<!--
<form method="post">
<div class="form-actions">
	<button class="btn btn-outline-secondary" name="a" type="submit" value="sync"><i class="fas fa-sync"></i> Sync</button>
	<button class="btn btn-outline-primary" name="a" type="submit" value="save"><i class="fas fa-save"></i> Modify</button>
	<button class="btn btn-outline-secondary" name="a" type="submit" value="mute"><i class="fas fa-ban"></i> Mute</button>
	<button class="btn btn-outline-danger" name="a" type="submit" value="void"><i class="fas fa-trash"></i> Void</button>
</div>
</form>
-->

<?= $this->block('modal-coa-upload.php') ?>
<?= $this->block('modal-send-email.php') ?>
