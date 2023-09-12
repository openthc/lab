/**
 * Companion script for the create view.
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

// Enable read-only form fields
// $("#metric-wrap-general input,select").each(function() {
// 	$(this).attr("readonly", false);
// 	$(this).attr("disabled", false);
// });
$(".metric-wrap input,select").each(function() {
	$(this).attr("readonly", false);
	$(this).attr("disabled", false);
});

// // Disable the action button after user clicks
// $(".form-actions .btn").on('click', function(event) {
// 	$(this).attr('disable', 'disable');
// });

// Set initial values on potency result cards (cannabinoid profile)
// @deprecated
$(".potency-result-cards [class^='potency-result-']").each(function(index, spanElm) {
	$(spanElm).text('-.--%');
});
