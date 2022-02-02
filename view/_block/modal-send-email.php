<?php
/**
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

?>

<div class="modal" id="modal-result-email" role="dialog" tabindex="-1">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">

			<div class="modal-header">
				<h4 class="modal-title">Send Email</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body">
				<p>Send Lab results and QA data with anyone!</p>
				<p>
					<a href="mailto:?<?= $data['share_mail_link'] ?>">Click here to send from your own email!</a>
				</p>

				<div class="form-group">
					<label>To:</label>
					<input class="form-control" name="email-rcpt">
				</div>
				<div class="form-group">
					<label>Subject:</label>
					<input class="form-control" name="email-subj">
				</div>
				<div class="form-group">
					<textarea class="form-control" name="email-body"></textarea>
				</div>
			</div>

			<div class="modal-footer">
				<button class="btn btn-outline-primary" name="a" type="submit" value="email-send"><i class="fas fa-send-o"></i> Send</button>
			</div>

		</div>
	</div>
</div>
