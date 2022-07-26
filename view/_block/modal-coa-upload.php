<?php
/**
 * Modal Dialog for Upload
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

?>

<form enctype="multipart/form-data" method="post">
<div class="modal" id="modal-coa-upload" role="dialog" tabindex="-1">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">

			<div class="modal-header">
				<h4 class="modal-title">COA Upload</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body">
				<p>This Result doesn't have a COA uploaded.</p>
				<p>You can upload one here, to make it visible to retailers and consumers.</p>
				<div class="form-group">
					<input class="form-control" name="file" type="file" >
				</div>
				<!--
				<hr>

				<div class="form-group">
					<label>Request from Vendor or Lab</label>
					<div class="input-group">
						<input class="form-control" id="coa-upload-link" readonly value="">
						<a
							class="btn btn-outline-secondary disabled"
							disabled
							href=""
							id="coa-upload-mail"
							target="_blank"><i class="fas fa-envelope-open-text"></i></a>
						<button
							class="btn btn-outline-secondary btn-clipcopy"
							disabled
							data-clipboard-text=""
							id="coa-upload-copy"
							type="button"><i class="fas fa-clipboard"></i></button>
					</div>
				</div>
				-->
			</div>

		<div class="modal-footer">
			<button class="btn btn-primary" name="a" type="submit" value="coa-upload"><i class="fas fa-upload"></i> Upload</button>
		</div>

		</div>
	</div>
</div>
</form>
