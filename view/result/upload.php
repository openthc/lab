<?php
/**
 * Upload Dropzone for Results
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

?>

<div style="position:relative;">
<div style="position:absolute; right: 0.25em; top: 0.25em;">
	<?php
	if ('lab-bulk' != $data['mode']) {
		echo '<a class="btn btn-outline-secondary" href="/result/upload/queue"><i class="fas fa-rss"></i></a>';
	}
	?>
</div>
</div>


<style>
.upload-drop-zone {
	border: 4px inset #999;
	height: 400px;
	margin: 0 auto;
	position: relative;
	width: 80%;
}
.upload-drop-zone.active {
	background: #ccc;
	border: 4px inset #090;
}
.upload-drop-zone .upload-drop-hint {
	position: absolute;
	text-align:center;
	top: 20%;
	width: 100%;
}
.upload-drop-zone .progress {
	border-radius: 0;
	bottom: 0;
	position: absolute;
	width: 100%;
}
</style>


<div class="container mt-4" style="min-height: 70vh;">
<h1><?= $data['Page']['title'] ?></h1>
<?php
if ('lab-bulk' != $data['mode']) {
?>
	<div class="alert alert-warning">
		You are uploading files to the processing queue for <strong><?= __h($data['Company']['name']) ?></strong><br>
		<!-- <small>link expires: {{ x|date('m/d H:i') }}</small> -->
	</div>
<?php
}
?>

<div class="upload-drop-zone">

	<div class="upload-drop-hint">
		<h2>Drop Files Here</h2>
		<p>One or more files or folders can be dropped here to begin upload</p>
	</div>

	<div class="progress">
		<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">0%</div>
	</div>

	<div style="display:none;">
		<input id="upload-file" name="file" type="file" multiple>
	</div>

</div>

<?php
if ('lab-bulk' != $data['mode']) {
?>
<div>
	<p>Request from Lab</p>
	<div class="form-group">
		<div class="input-group">
			<input class="form-control" readonly value="https://<?= $data['Site']['hostname'] ?>/intent?_=<?= $data['coa_upload_hash'] ?>">
			<a class="btn btn-outline-secondary" href="mailto:?subject=Upload%20Lab%20Results&amp;body=Please%20upload%20my%20lab%20results%20to%20this%20page%3A%0A%0A%20%20https%3A%2F%2F<?= $data['Site']['hostname'] ?>%2Fintent%3F_%3D<?= $data['coa_upload_hash'] ?>"><i class="fas fa-envelope-open-text"></i></a>
			<button class="btn btn-outline-secondary btn-clipcopy" data-clipboard-text="https://<?= $data['Site']['hostname'] ?>/intent?_=<?= $data['coa_upload_hash'] ?>" type="button"><i class="fas fa-clipboard"></i></button>
		</div>
	</div>
</div>
<?php
}
?>
</div>

<!-- @todo Drop this, use more vanilla -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/blueimp-file-upload/9.32.0/js/jquery.fileupload.min.js" integrity="sha256-V7oMvrVtCc+WNUE+b2tjctJ2eBS1b5ct+UiPYvsgPxs=" crossorigin="anonymous"></script>
<script>
$(function() {

	$('#upload-file').fileupload({
		dataType: 'json',
		dropZone: $('.upload-drop-zone'),
		done: function(e, data) {
			$('.upload-drop-zone').removeClass('active');
		},
		progressall: function (e, data) {
			var pct = parseInt(data.loaded / data.total * 100, 10);
			$('.upload-drop-zone .progress-bar').css('width', pct + '%').text(pct + '%');
		},
	});

	$('.upload-drop-zone').on('dragover', function(e) {
		$('.upload-drop-zone .progress-bar').css('width', '0%').text('0%');
		$(this).addClass('active');
		return false;
	});

	$('.upload-drop-zone').on('dragleave', function(e) {
		$(this).removeClass('active');
	});

	// Prevent accidental drops
	$(document).bind('drop dragover', function (e) {
		e.preventDefault();
	});

});
</script>
