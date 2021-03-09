<?php
/**
 * Public View of a Lab Result
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
</style>


<div class="container mt-4">
<h1>Result: <?= __h($data['Result']['id']) ?></h1>
<h3>Sample: <?= __h($data['Sample']['id']) ?></h3>

<?= $this->block('product-summary') ?>

<div class="row">
<div class="col-md-8">
	<div class="form-group">
		<label>Share Link</label>
		<div class="input-group">
			<input class="form-control" readonly value="https://<?= $data['Site']['hostname'] ?>/share/<?= $data['Result']['id'] ?>.html">
			<div class="input-group-append">
				<button class="btn btn-outline-secondary btn-clipcopy" data-clipboard-text="https://<?= $data['Site']['hostname'] ?>/share/<?= $data['Result']['id'] ?>.html" type="button"><i class="fas fa-clipboard"></i></button>
			</div>
		</div>
	</div>
</div>
<div class="col-md-4">
	<div class="form-group">
		<label>Print Link
			<span data-toggle="tooltip" data-placement="top" style="cursor:help;" title="Waiting for the Product Owner or Laboratory to upload these documents">
				<i class="fas fa-info-circle"></i>
			</span>
		</label>
		<div class="input-group">
			<?php
			if ($data['Result']['coa_file']) {
			?>
				<a class="btn btn-block btn-outline-primary" href="https://<?= $data['Site']['hostname'] ?>/share/<?= $data['Result']['id'] ?>.pdf" target="_blank"><i class="fas fa-print"></i> Print COA</a>
			<?php
			} else {
				if ($data['mine']) {
				?>
					<a
						class="btn btn-block btn-outline-warning"
						href="#"
						x-href="https://<?= $data['Site']['hostname'] ?>/result/<?= $data['Result']['id'] ?>"
						data-toggle="modal"
						data-target="#modal-coa-upload"
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

<?= $this->block('potency-summary') ?>

<hr>

<div class="metric-general-wrap" style="border: 1px solid #999; margin-bottom: 1rem;">
	<?= $this->block('coa/general.html') ?>
</div>

<div class="d-flex flex-row flex-fill" style="margin-bottom: 1rem;">

	<div style="border: 1px solid #999; flex: 1 1 50%;">
		<?= $this->block('coa/cannabinoid.html') ?>
	</div>

	<div style="border: 1px solid #999; flex: 1 1 50%;">
		<?= $this->block('coa/terpene-mini.html') ?>
	</div>
</div>

<div class="d-flex flex-row flex-fill" style="margin-bottom: 1rem;">

	<div style="border: 1px solid #999; flex: 1 1 50%;">
		<?= $this->block('coa/solvent.html') ?>
	</div>

	<div style="border: 1px solid #999; flex: 1 1 50%;">
		<?= $this->block('coa/microbe.html') ?>
		<?= $this->block('coa/heavy-metal.html') ?>
	</div>
</div>


<!-- <div class="form-actions">
	<button class="btn btn-outline-primary" name="a" data-toggle="modal" data-target="#modal-result-email" type="button"><i class="far fa-envelope"></i> Email</button>
	<button class="btn btn-outline-primary" data-toggle="modal" data-target="#modal-scan-qr" type="button"><i class="fas fa-qrcode"></i> QR Code</button>
	<a class="btn btn-outline-secondary" href="https://<?= $data['Site']['hostname'] ?>/share/<?= $data['Result']['id'] ?>.json"> JSON</a>
</div> -->

</div>

<?= $this->block('modal-scan-qr.html') ?>
<?= $this->block('modal-send-email.html') ?>


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
