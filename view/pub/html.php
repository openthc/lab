<?php
/**
 * Public View of a Lab Result
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

?>

<style>
.metric-general-wrap h2 {
	background: #343434;
	color: #fefefe;
	margin: 0 0 0.50rem 0;
	padding: 0.25rem 0.50rem;
}

.metric-general-wrap .col {
	text-align: center;
}

/* Backported? */
.metric-section {
	flex: 1 1 50%;
	margin: 0;
	padding: 0 0.50rem 1rem 0.50rem;
	min-width: 20em;
}

</style>


<div class="container mt-4">

<h1>Result: <?= __h($data['Result']['id_nice']) ?></h1>
<h2>Sample: <?= __h($data['Sample']['id_nice']) ?></h2>

<?= $this->block('product-summary.php') ?>

<div class="row">
<div class="col-md-8">
	<div class="mb-2">
		<label>Share Link</label>
		<div class="input-group">
			<input class="form-control" readonly value="https://<?= $data['Site']['hostname'] ?>/pub/<?= $data['Result']['id'] ?>.html">
			<button class="btn btn-outline-secondary btn-clipcopy" data-clipboard-text="https://<?= $data['Site']['hostname'] ?>/pub/<?= $data['Result']['id'] ?>.html" type="button"><i class="fas fa-clipboard"></i></button>
		</div>
	</div>
</div>
<div class="col-md-4">
	<div class="mb-2">
		<label>Print Link
			<span data-toggle="tooltip" data-placement="top" style="cursor:help;" title="Waiting for the Product Owner or Laboratory to upload these documents">
				<i class="fas fa-info-circle"></i>
			</span>
		</label>
		<div class="input-group">
			<?php
			if ($data['Result']['coa_file']) {
			?>
				<a class="btn btn-block btn-outline-primary" href="https://<?= $data['Site']['hostname'] ?>/pub/<?= $data['Result']['id'] ?>.pdf" target="_blank"><i class="fas fa-print"></i> Print COA</a>
			<?php
			} else {
				if ($data['mine']) {
				?>
					<a
						class="btn btn-block btn-outline-warning"
						href="#"
						x-href="https://<?= $data['Site']['hostname'] ?>/result/<?= $data['Result']['id'] ?>"
						data-bs-toggle="modal"
						data-bs-target="#modal-coa-upload"
						title="Upload the PDF COA Documents"><i class="fas fa-print"></i> Upload COA</a>
				<?php
				} else {
				?>

					<div class="btn btn-block btn-outline-secondary disabled"><i class="fas fa-print"></i> Waiting for Documents</div>
				<?php
				}
			}
			?>
		</div>
	</div>
</div>
</div>

<?= $this->block('potency-summary.php') ?>

<hr>

<div class="metric-general-wrap" style="border: 1px solid #999; margin-bottom: 1rem;">
	<?= $this->block('coa/general.php') ?>
</div>

<div class="d-flex flex-row flex-fill" style="margin-bottom: 1rem;">

	<div class="metric-section">
		<?= $this->block('coa/cannabinoid.php') ?>
	</div>

	<div class="metric-section">
		<?= $this->block('coa/terpene-mini.php') ?>
	</div>
</div>

<div class="d-flex flex-row flex-fill" style="margin-bottom: 1rem;">

	<div class="metric-section">
		<?= $this->block('coa/solvent.php') ?>
	</div>

	<div class="metric-section">
		<?= $this->block('coa/microbe.php') ?>
		<?= $this->block('coa/heavy-metal.php') ?>
	</div>
</div>


<!-- <div class="form-actions">
	<button class="btn btn-outline-primary" name="a" data-bs-toggle="modal" data-bs-target="#modal-result-email" type="button"><i class="far fa-envelope"></i> Email</button>
	<button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-scan-qr" type="button"><i class="fas fa-qrcode"></i> QR Code</button>
	<a class="btn btn-outline-secondary" href="https://<?= $data['Site']['hostname'] ?>/pub/<?= $data['Result']['id'] ?>.json"> JSON</a>
</div> -->

</div>

<?= $this->block('modal-scan-qr.php') ?>
<?= $this->block('modal-send-email.php') ?>


<script>
$(function() {
	//$('div.collapse').addClass('show');
	$('#metric-wrap-cb').addClass('show');
});
</script>

<?php
if ($data['mine']) {
	$this->block('modal-coa-upload.html');
?>
<script>
$(function() {

	$('#modal-coa-upload').on('shown.bs.modal', function() {

		var arg = {
			a: 'coa-upload-link',
		};

		$.post('/result/<?= $data['Result']['id'] ?>', arg)

			.done(function(body, code) {

				var url_link = new URL('/intent', window.location);
				url_link.search = new URLSearchParams({
					_: body.data
				});

				var url_mail = new URL('mailto:');
				url_mail.search = new URLSearchParams({
					subject: 'Upload Lab Results',
					body: "Please upload the COA for <?= $data['Result']['id'] ?> to this page:\n\n  " + url_link.toString()
				});

				$('#coa-upload-link').val( url_link.toString() );
				$('#coa-upload-copy').attr('data-clipboard-text', url_link.toString() );
				$('#coa-upload-mail').attr('href', url_mail.toString() );
				// mailto:?subject=&amp;body=
				// https://<?= $data['Site']['hostname'] ?>/intent?_=<?= $data['coa_upload_hash'] ?>
				// https://<?= $data['Site']['hostname'] ?>/intent?_=<?= $data['coa_upload_hash'] ?>
				// https://<?= $data['Site']['hostname'] ?>/intent?_=<?= $data['coa_upload_hash'] ?>
			})
			.always(function(a, b) {

				var n, x;

				n = $('#coa-upload-copy');
				x = n.data('clipboard-text');
				if (x) {
					n.attr('disabled', false);
					n.removeClass('disabled');
				}

				n = $('#coa-upload-mail');
				x = n.attr('href');
				if (x) {
					n.attr('disabled', false);
					n.removeClass('disabled');
				}

			});

	});

});
</script>
<?php
}
